#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const Logger = require('./utils/logger');

const logger = new Logger('SyncActivation');

// Constants
const STAGING_SITE_URL = process.env.STAGING_SITE_URL;
const STAGING_SITE_AUTH_TOKEN = process.env.STAGING_SITE_AUTH_TOKEN;
const LIVE_SITE_URL = process.env.LIVE_SITE_URL;
const LIVE_SITE_AUTH_TOKEN = process.env.LIVE_SITE_AUTH_TOKEN;

// List of known security plugins with exact slugs
const SECURITY_PLUGINS = [
    'wordfence',
    'better-wp-security',
    'ithemes-security-pro',
    'sucuri-scanner',
    'all-in-one-wp-security-and-firewall',
    'wp-security-audit-log',
    'shield-security',
    'wp-hide-security-enhancer',
    'bulletproof-security',
    'security-ninja',
    'defender-security'
];

// Function to check if a plugin is a security plugin
function isSecurityPlugin(pluginSlug) {
    if (!pluginSlug) return false;
    // Remove .php extension and normalize slug
    const normalizedSlug = pluginSlug.toLowerCase().replace(/\.php$/, '');
    return SECURITY_PLUGINS.some(securityPlugin => 
        normalizedSlug === securityPlugin || 
        normalizedSlug.includes(securityPlugin)
    );
}

// Verify required environment variables with detailed logging
function validateEnvironment() {
    const missingVars = [];
    if (!STAGING_SITE_URL) missingVars.push('STAGING_SITE_URL');
    if (!STAGING_SITE_AUTH_TOKEN) missingVars.push('STAGING_SITE_AUTH_TOKEN');
    if (!LIVE_SITE_URL) missingVars.push('LIVE_SITE_URL');
    if (!LIVE_SITE_AUTH_TOKEN) missingVars.push('LIVE_SITE_AUTH_TOKEN');

    if (missingVars.length > 0) {
        const errorMsg = `Missing required environment variables: ${missingVars.join(', ')}`;
        logger.error(errorMsg);
        throw new Error(errorMsg);
    }

    logger.info('Environment validation successful', {
        stagingUrl: STAGING_SITE_URL,
        liveUrl: LIVE_SITE_URL
    });
}

async function makeApiRequest(url, options, retries = 3) {
    for (let attempt = 1; attempt <= retries; attempt++) {
        try {
            logger.info('Making API request', { 
                url, 
                method: options.method || 'GET',
                attempt: `${attempt}/${retries}`
            });

            const response = await fetch(url, {
                ...options,
                headers: {
                    ...options.headers,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                const errorBody = await response.text();
                const errorDetails = {
                    status: response.status,
                    statusText: response.statusText,
                    body: errorBody,
                    url,
                    method: options.method || 'GET',
                    attempt
                };

                if (response.status === 401) {
                    logger.error('Authentication failed', errorDetails);
                    throw new Error('Authentication failed. Please check your credentials.');
                }

                logger.error('API request failed', errorDetails);
                
                if (attempt === retries) {
                    throw new Error(`API request failed after ${retries} attempts: ${response.status} - ${errorBody}`);
                }
                
                // Wait before retrying (exponential backoff)
                await new Promise(resolve => setTimeout(resolve, Math.pow(2, attempt) * 1000));
                continue;
            }

            const data = await response.json();
            logger.debug('API request successful', { 
                url, 
                status: response.status,
                attempt 
            });
            return data;
        } catch (error) {
            if (attempt === retries) {
                logger.error('API request error', {
                    url,
                    error: error.message,
                    stack: error.stack,
                    finalAttempt: true
                });
                throw error;
            }
            logger.warn(`API request attempt ${attempt} failed, retrying...`, {
                url,
                error: error.message
            });
            await new Promise(resolve => setTimeout(resolve, Math.pow(2, attempt) * 1000));
        }
    }
}

async function activatePlugin(pluginSlug) {
    try {
        logger.info(`Attempting to activate plugin: ${pluginSlug}`);
        const result = await makeApiRequest(
            `${STAGING_SITE_URL}/wp-json/techops/v1/plugins/activate/${pluginSlug}`,
            {
                method: 'POST',
                headers: {
                    'Authorization': `Basic ${STAGING_SITE_AUTH_TOKEN}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ plugin: pluginSlug })
            }
        );

        if (!result.success) {
            throw new Error(`Failed to activate plugin: ${pluginSlug}`);
        }

        logger.info(`Successfully activated plugin: ${pluginSlug}`);
        return true;
    } catch (error) {
        logger.error(`Plugin activation failed: ${pluginSlug}`, {
            error: error.message,
            stack: error.stack
        });
        throw error;
    }
}

async function deactivatePlugin(pluginSlug) {
    try {
        logger.info(`Attempting to deactivate plugin: ${pluginSlug}`);
        const result = await makeApiRequest(
            `${STAGING_SITE_URL}/wp-json/techops/v1/plugins/deactivate/${pluginSlug}`,
            {
                method: 'POST',
                headers: {
                    'Authorization': `Basic ${STAGING_SITE_AUTH_TOKEN}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ plugin: pluginSlug })
            }
        );

        if (!result.success) {
            throw new Error(`Failed to deactivate plugin: ${pluginSlug}`);
        }

        logger.info(`Successfully deactivated plugin: ${pluginSlug}`);
        return true;
    } catch (error) {
        logger.error(`Plugin deactivation failed: ${pluginSlug}`, {
            error: error.message,
            stack: error.stack
        });
        throw error;
    }
}

async function syncActivationStates() {
    try {
        // Validate environment first
        validateEnvironment();

        logger.info('Starting activation sync process');

        // Get current activation states from staging site
        const stagingPlugins = await makeApiRequest(
            `${STAGING_SITE_URL}/wp-json/techops/v1/plugins/list`,
            {
                headers: {
                    'Authorization': `Basic ${STAGING_SITE_AUTH_TOKEN}`
                }
            }
        );

        // Validate response format
        if (!Array.isArray(stagingPlugins)) {
            throw new Error(`Invalid API response format. Expected array, got: ${typeof stagingPlugins}`);
        }

        logger.info(`Retrieved ${stagingPlugins.length} plugins from staging site`);
        const stagingStates = new Map(stagingPlugins.map(p => [p.slug, p.active]));

        // Ensure directory exists before reading/writing files
        const wpContentDir = path.join(__dirname, '..', 'wp-content');
        const pluginsDir = path.join(wpContentDir, 'plugins');
        
        if (!fs.existsSync(wpContentDir)) {
            fs.mkdirSync(wpContentDir, { recursive: true });
            logger.info('Created wp-content directory');
        }
        if (!fs.existsSync(pluginsDir)) {
            fs.mkdirSync(pluginsDir, { recursive: true });
            logger.info('Created plugins directory');
        }

        // Read desired activation states
        const activationStatesPath = path.join(pluginsDir, 'activation-states.json');
        let activationStates;
        try {
            activationStates = JSON.parse(fs.readFileSync(activationStatesPath, 'utf8'));
            logger.info('Successfully read activation states file', {
                path: activationStatesPath,
                pluginCount: activationStates.plugins.length
            });
        } catch (error) {
            logger.error('Failed to read activation states file', {
                path: activationStatesPath,
                error: error.message,
                stack: error.stack
            });
            throw error;
        }

        // Track changes for reporting
        const changes = {
            activated: [],
            deactivated: [],
            errors: [],
            skipped: [],
            timestamp: new Date().toISOString()
        };

        // Separate plugins into regular and security plugins
        const regularPlugins = [];
        const securityPlugins = [];
        
        activationStates.plugins.forEach(plugin => {
            if (!plugin.slug) {
                logger.warn('Found plugin without slug in activation states', { plugin });
                return;
            }

            if (isSecurityPlugin(plugin.slug)) {
                logger.info(`Identified security plugin: ${plugin.slug}`);
                securityPlugins.push(plugin);
            } else {
                regularPlugins.push(plugin);
            }
        });

        logger.info('Plugin categorization complete', {
            regularCount: regularPlugins.length,
            securityCount: securityPlugins.length
        });

        // Process regular plugins first
        for (const plugin of regularPlugins) {
            try {
                const currentState = stagingStates.get(plugin.slug);
                if (currentState === undefined) {
                    logger.warn(`Plugin not found in staging site: ${plugin.slug}`);
                    changes.skipped.push({
                        slug: plugin.slug,
                        reason: 'Plugin not found in staging site'
                    });
                    continue;
                }

                if (currentState !== plugin.active) {
                    if (plugin.active) {
                        await activatePlugin(plugin.slug);
                        changes.activated.push(plugin.slug);
                    } else {
                        await deactivatePlugin(plugin.slug);
                        changes.deactivated.push(plugin.slug);
                    }
                } else {
                    logger.debug(`Plugin ${plugin.slug} already in desired state (${plugin.active ? 'active' : 'inactive'})`);
                }
            } catch (error) {
                logger.error(`Failed to process plugin: ${plugin.slug}`, {
                    error: error.message,
                    stack: error.stack
                });
                changes.errors.push({
                    slug: plugin.slug,
                    error: error.message
                });
            }
        }

        // Process security plugins last
        for (const plugin of securityPlugins) {
            try {
                const currentState = stagingStates.get(plugin.slug);
                if (currentState === undefined) {
                    logger.warn(`Security plugin not found in staging site: ${plugin.slug}`);
                    changes.skipped.push({
                        slug: plugin.slug,
                        reason: 'Security plugin not found in staging site'
                    });
                    continue;
                }

                if (currentState !== plugin.active) {
                    if (plugin.active) {
                        await activatePlugin(plugin.slug);
                        changes.activated.push(plugin.slug);
                    } else {
                        await deactivatePlugin(plugin.slug);
                        changes.deactivated.push(plugin.slug);
                    }
                } else {
                    logger.debug(`Security plugin ${plugin.slug} already in desired state (${plugin.active ? 'active' : 'inactive'})`);
                }
            } catch (error) {
                logger.error(`Failed to process security plugin: ${plugin.slug}`, {
                    error: error.message,
                    stack: error.stack
                });
                changes.errors.push({
                    slug: plugin.slug,
                    error: error.message
                });
            }
        }

        // Write sync report
        const reportPath = path.join(pluginsDir, 'sync-report.json');
        const report = {
            timestamp: changes.timestamp,
            changes: changes,
            stats: {
                total: regularPlugins.length + securityPlugins.length,
                activated: changes.activated.length,
                deactivated: changes.deactivated.length,
                errors: changes.errors.length,
                skipped: changes.skipped.length
            }
        };

        try {
            fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
            logger.info('Sync report written successfully', { path: reportPath });
        } catch (error) {
            logger.error('Failed to write sync report', {
                path: reportPath,
                error: error.message,
                stack: error.stack
            });
            throw error;
        }

        // Log final status
        if (changes.errors.length > 0) {
            const errorMsg = `Sync completed with ${changes.errors.length} errors`;
            logger.error(errorMsg, { errors: changes.errors });
            throw new Error(errorMsg);
        }

        logger.info('Sync completed successfully', {
            activated: changes.activated.length,
            deactivated: changes.deactivated.length,
            skipped: changes.skipped.length
        });

        return changes;
    } catch (error) {
        logger.error('Sync process failed', {
            error: error.message,
            stack: error.stack
        });
        throw error;
    }
}

// Execute if run directly
if (require.main === module) {
    syncActivationStates().catch(error => {
        console.error('Fatal error:', error);
        process.exit(1);
    });
}

module.exports = syncActivationStates; 