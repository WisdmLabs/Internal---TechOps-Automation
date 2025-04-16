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

async function makeApiRequest(url, options) {
    try {
        logger.info('Making API request', { url, method: options.method || 'GET' });
        const response = await fetch(url, options);
        
        if (!response.ok) {
            const errorBody = await response.text();
            const errorDetails = {
                status: response.status,
                statusText: response.statusText,
                body: errorBody,
                url,
                method: options.method || 'GET'
            };
            logger.error('API request failed', errorDetails);
            throw new Error(`API request failed: ${response.status} - ${errorBody}`);
        }

        const data = await response.json();
        logger.debug('API request successful', { url, status: response.status });
        return data;
    } catch (error) {
        logger.error('API request error', {
            url,
            error: error.message,
            stack: error.stack
        });
        throw error;
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

        // Read desired activation states
        const activationStatesPath = path.join(__dirname, '..', 'wp-content/plugins/activation-states.json');
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

        const changes = {
            activated: [],
            deactivated: [],
            errors: [],
            skipped: []
        };

        // Sync each plugin's activation state
        for (const plugin of activationStates.plugins) {
            try {
                const currentState = stagingStates.get(plugin.slug);
                if (currentState === undefined) {
                    logger.warn(`Plugin not found on staging site: ${plugin.slug}`);
                    changes.skipped.push({
                        plugin: plugin.slug,
                        reason: 'Plugin not found on staging site'
                    });
                    continue;
                }

                if (currentState !== plugin.active) {
                    logger.info(`State mismatch for plugin: ${plugin.slug}`, {
                        current: currentState,
                        desired: plugin.active
                    });

                    if (plugin.active) {
                        await activatePlugin(plugin.slug);
                        changes.activated.push(plugin.slug);
                    } else {
                        await deactivatePlugin(plugin.slug);
                        changes.deactivated.push(plugin.slug);
                    }
                } else {
                    logger.debug(`Plugin state already correct: ${plugin.slug}`);
                    changes.skipped.push({
                        plugin: plugin.slug,
                        reason: 'State already correct'
                    });
                }
            } catch (error) {
                logger.error(`Failed to sync plugin: ${plugin.slug}`, {
                    error: error.message,
                    stack: error.stack
                });
                changes.errors.push({
                    plugin: plugin.slug,
                    error: error.message,
                    stack: error.stack
                });
            }
        }

        // Write sync report
        const report = {
            timestamp: new Date().toISOString(),
            changes,
            summary: {
                total: activationStates.plugins.length,
                activated: changes.activated.length,
                deactivated: changes.deactivated.length,
                errors: changes.errors.length,
                skipped: changes.skipped.length
            }
        };

        const reportPath = path.join(__dirname, '..', 'wp-content/plugins/sync-report.json');
        try {
            fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
            logger.info('Successfully wrote sync report', { path: reportPath });
        } catch (error) {
            logger.error('Failed to write sync report', {
                path: reportPath,
                error: error.message,
                stack: error.stack
            });
            throw error;
        }

        if (changes.errors.length > 0) {
            const errorMsg = `Sync completed with ${changes.errors.length} errors`;
            logger.error(errorMsg, { errors: changes.errors });
            throw new Error(errorMsg);
        }

        logger.info('Activation sync completed successfully', report);
        return report;
    } catch (error) {
        logger.error('Activation sync failed', {
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