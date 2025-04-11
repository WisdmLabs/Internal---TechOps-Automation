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

async function syncActivationStates() {
    try {
        console.log('Starting activation state synchronization...');
        
        // Read activation states from live site
        const liveStatesPath = path.join('wp-content', 'plugins', 'activation-states.json');
        if (!fs.existsSync(liveStatesPath)) {
            console.error('Activation states file not found:', liveStatesPath);
            process.exit(1);
        }
        
        const liveStates = JSON.parse(fs.readFileSync(liveStatesPath));
        console.log(`Found ${liveStates.plugins.length} plugins in live site`);
        
        // Get current activation states from staging
        console.log('Fetching current plugin states from staging site...');
        const stagingResponse = execSync(
            `curl -s -H "Authorization: Basic ${process.env.STAGING_SITE_AUTH_TOKEN}" ` +
            `"${process.env.STAGING_SITE_URL}/wp-json/techops/v1/plugins/list"`
        ).toString();
        
        const stagingPlugins = JSON.parse(stagingResponse);
        console.log(`Found ${stagingPlugins.length} plugins in staging site`);
        
        // Track changes for reporting
        const changes = {
            activated: [],
            deactivated: [],
            errors: []
        };
        
        // Compare and activate/deactivate plugins
        for (const livePlugin of liveStates.plugins) {
            const stagingPlugin = stagingPlugins.find(p => p.slug === livePlugin.slug);
            
            if (stagingPlugin) {
                if (livePlugin.active !== stagingPlugin.active) {
                    const action = livePlugin.active ? 'activate' : 'deactivate';
                    console.log(`${action}ing plugin: ${livePlugin.slug}`);
                    
                    try {
                        const response = execSync(
                            `curl -s -X POST -H "Authorization: Basic ${process.env.STAGING_SITE_AUTH_TOKEN}" ` +
                            `-H "Content-Type: application/json" ` +
                            `-d '{"plugin":"${livePlugin.slug}"}' ` +
                            `"${process.env.STAGING_SITE_URL}/wp-json/techops/v1/plugins/${action}"`
                        ).toString();
                        
                        const result = JSON.parse(response);
                        if (result.success) {
                            changes[action + 'd'].push(livePlugin.slug);
                            console.log(`Successfully ${action}d plugin: ${livePlugin.slug}`);
                        } else {
                            changes.errors.push({
                                plugin: livePlugin.slug,
                                action: action,
                                error: result.message || 'Unknown error'
                            });
                            console.error(`Failed to ${action} plugin ${livePlugin.slug}: ${result.message}`);
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
            }
        }
        
        // Write sync report
        const report = {
            timestamp: new Date().toISOString(),
            changes: changes,
            summary: {
                total: liveStates.plugins.length,
                activated: changes.activated.length,
                deactivated: changes.deactivated.length,
                errors: changes.errors.length
            }
        };
        
        fs.writeFileSync(
            path.join('wp-content', 'plugins', 'sync-report.json'),
            JSON.stringify(report, null, 2)
        );
        
        console.log('\nSync Report:');
        console.log(`Total plugins processed: ${report.summary.total}`);
        console.log(`Activated: ${report.summary.activated}`);
        console.log(`Deactivated: ${report.summary.deactivated}`);
        console.log(`Errors: ${report.summary.errors}`);
        
        if (report.summary.errors > 0) {
            console.error('\nErrors encountered:');
            report.changes.errors.forEach(error => {
                console.error(`- ${error.plugin}: ${error.error}`);
            });
            process.exit(1);
        }
        
        console.log('\nActivation states synchronized successfully');
    } catch (error) {
        console.error('Error syncing activation states:', error.message);
        process.exit(1);
    }
}

syncActivationStates(); 