#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Verify required environment variables
const requiredEnvVars = [
    'LIVE_SITE_AUTH_TOKEN',
    'STAGING_SITE_AUTH_TOKEN',
    'LIVE_SITE_URL',
    'STAGING_SITE_URL'
];

for (const envVar of requiredEnvVars) {
    if (!process.env[envVar]) {
        console.error(`Error: Required environment variable ${envVar} is not set`);
        process.exit(1);
    }
}

// Function to make API request with retries
async function makeApiRequest(url, token, method = 'GET', data = null) {
    const maxRetries = 3;
    let retryCount = 0;
    
    while (retryCount < maxRetries) {
        try {
            const command = [
                'curl -s',
                `-H "Authorization: Basic ${token}"`,
                '-H "Content-Type: application/json"',
                '-H "Accept: application/json"',
                '--max-time 30'
            ];
            
            if (method === 'POST') {
                command.push('-X POST');
                if (data) {
                    command.push(`-d '${JSON.stringify(data)}'`);
                }
            }
            
            command.push(`"${url}"`);
            
            const response = execSync(command.join(' ')).toString();
            return JSON.parse(response);
        } catch (error) {
            retryCount++;
            if (retryCount === maxRetries) {
                throw error;
            }
            console.log(`Request failed, retrying in 5 seconds... (Attempt ${retryCount} of ${maxRetries})`);
            await new Promise(resolve => setTimeout(resolve, 5000));
        }
    }
}

// Function to validate plugin slug
function validatePluginSlug(slug) {
    return /^[a-z0-9-]+$/.test(slug);
}

async function syncActivationStates() {
    try {
        console.log('Starting activation state synchronization...');
        
        // Read activation states from live site
        const liveStatesPath = path.join('wp-content', 'plugins', 'activation-states.json');
        if (!fs.existsSync(liveStatesPath)) {
            console.error('Activation states file not found:', liveStatesPath);
            process.exit(1);
        }
        
        let liveStates;
        try {
            liveStates = JSON.parse(fs.readFileSync(liveStatesPath));
            if (!liveStates.plugins || !Array.isArray(liveStates.plugins)) {
                throw new Error('Invalid activation states file format');
            }
        } catch (error) {
            console.error('Error parsing activation states file:', error.message);
            process.exit(1);
        }
        
        console.log(`Found ${liveStates.plugins.length} plugins in live site`);
        
        // Get current activation states from staging
        console.log('Fetching current plugin states from staging site...');
        let stagingPlugins;
        try {
            stagingPlugins = await makeApiRequest(
                `${process.env.STAGING_SITE_URL}/wp-json/techops/v1/plugins/list`,
                process.env.STAGING_SITE_AUTH_TOKEN
            );
            if (!Array.isArray(stagingPlugins)) {
                throw new Error('Invalid response format from staging site');
            }
        } catch (error) {
            console.error('Error fetching staging plugins:', error.message);
            process.exit(1);
        }
        
        console.log(`Found ${stagingPlugins.length} plugins in staging site`);
        
        // Track changes for reporting
        const changes = {
            activated: [],
            deactivated: [],
            errors: []
        };
        
        // Compare and activate/deactivate plugins
        for (const livePlugin of liveStates.plugins) {
            if (!validatePluginSlug(livePlugin.slug)) {
                changes.errors.push({
                    plugin: livePlugin.slug,
                    action: 'validate',
                    error: 'Invalid plugin slug format'
                });
                continue;
            }
            
            const stagingPlugin = stagingPlugins.find(p => p.slug === livePlugin.slug);
            
            if (stagingPlugin) {
                if (livePlugin.active !== stagingPlugin.active) {
                    const action = livePlugin.active ? 'activate' : 'deactivate';
                    console.log(`${action}ing plugin: ${livePlugin.slug}`);
                    
                    try {
                        const response = await makeApiRequest(
                            `${process.env.STAGING_SITE_URL}/wp-json/techops/v1/plugins/${action}`,
                            process.env.STAGING_SITE_AUTH_TOKEN,
                            'POST',
                            { plugin: livePlugin.slug }
                        );
                        
                        if (response.success) {
                            changes[action + 'd'].push(livePlugin.slug);
                            console.log(`Successfully ${action}d plugin: ${livePlugin.slug}`);
                        } else {
                            changes.errors.push({
                                plugin: livePlugin.slug,
                                action: action,
                                error: response.message || 'Unknown error'
                            });
                            console.error(`Failed to ${action} plugin ${livePlugin.slug}: ${response.message}`);
                        }
                    } catch (error) {
                        changes.errors.push({
                            plugin: livePlugin.slug,
                            action: action,
                            error: error.message
                        });
                        console.error(`Error ${action}ing plugin ${livePlugin.slug}:`, error.message);
                    }
                }
            } else {
                console.warn(`Plugin ${livePlugin.slug} not found in staging site`);
                changes.errors.push({
                    plugin: livePlugin.slug,
                    action: 'sync',
                    error: 'Plugin not found in staging site'
                });
            }
        }
        
        // Write sync report
        const report = {
            timestamp: new Date().toISOString(),
            changes: changes,
            summary: {
                total_processed: liveStates.plugins.length,
                activated: changes.activated.length,
                deactivated: changes.deactivated.length,
                errors: changes.errors.length
            }
        };
        
        fs.writeFileSync(
            path.join('wp-content', 'plugins', 'sync-report.json'),
            JSON.stringify(report, null, 2)
        );
        
        console.log('Sync report written to wp-content/plugins/sync-report.json');
        
        if (changes.errors.length > 0) {
            console.warn(`Completed with ${changes.errors.length} errors`);
            process.exit(1);
        }
        
        console.log('Activation state synchronization completed successfully');
    } catch (error) {
        console.error('Fatal error during synchronization:', error.message);
        process.exit(1);
    }
}

syncActivationStates(); 