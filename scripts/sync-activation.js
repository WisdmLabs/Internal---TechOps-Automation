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

// Verify required environment variables
if (!STAGING_SITE_URL || !STAGING_SITE_AUTH_TOKEN || !LIVE_SITE_URL || !LIVE_SITE_AUTH_TOKEN) {
    throw new Error('Missing required environment variables');
}

async function activatePlugin(pluginSlug) {
    try {
        const response = await fetch(`${STAGING_SITE_URL}/wp-json/techops/v1/plugins/activate/${pluginSlug}`, {
            method: 'POST',
            headers: {
                'Authorization': `Basic ${STAGING_SITE_AUTH_TOKEN}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ plugin: pluginSlug })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        return result.success;
    } catch (error) {
        logger.error(`Failed to activate plugin ${pluginSlug}`, { error: error.message });
        throw error;
    }
}

async function deactivatePlugin(pluginSlug) {
    try {
        const response = await fetch(`${STAGING_SITE_URL}/wp-json/techops/v1/plugins/deactivate/${pluginSlug}`, {
            method: 'POST',
            headers: {
                'Authorization': `Basic ${STAGING_SITE_AUTH_TOKEN}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ plugin: pluginSlug })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        return result.success;
    } catch (error) {
        logger.error(`Failed to deactivate plugin ${pluginSlug}`, { error: error.message });
        throw error;
    }
}

async function syncActivationStates() {
    try {
        // Get current activation states from staging site
        const response = await fetch(`${STAGING_SITE_URL}/wp-json/techops/v1/plugins/list`, {
            headers: {
                'Authorization': `Basic ${STAGING_SITE_AUTH_TOKEN}`
            }
        });

        if (!response.ok) {
            throw new Error(`Failed to get plugin list: ${response.status}`);
        }

        const stagingPlugins = await response.json();
        const stagingStates = new Map(stagingPlugins.map(p => [p.slug, p.active]));

        // Read desired activation states
        const activationStatesPath = path.join(__dirname, '..', 'wp-content/plugins/activation-states.json');
        const activationStates = JSON.parse(fs.readFileSync(activationStatesPath, 'utf8'));

        const changes = {
            activated: [],
            deactivated: [],
            errors: []
        };

        // Sync each plugin's activation state
        for (const plugin of activationStates.plugins) {
            try {
                const currentState = stagingStates.get(plugin.slug);
                if (currentState !== plugin.active) {
                    if (plugin.active) {
                        await activatePlugin(plugin.slug);
                        changes.activated.push(plugin.slug);
                    } else {
                        await deactivatePlugin(plugin.slug);
                        changes.deactivated.push(plugin.slug);
                    }
                }
            } catch (error) {
                changes.errors.push({
                    plugin: plugin.slug,
                    error: error.message
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
                errors: changes.errors.length
            }
        };

        fs.writeFileSync(
            path.join(__dirname, '..', 'wp-content/plugins/sync-report.json'),
            JSON.stringify(report, null, 2)
        );

        logger.info('Activation sync completed', report);
        return report;
    } catch (error) {
        logger.error('Activation sync failed', { error: error.message });
        throw error;
    }
}

module.exports = syncActivationStates; 