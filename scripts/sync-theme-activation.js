#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const Logger = require('./utils/logger');

const logger = new Logger('SyncThemeActivation');

// Constants
const STAGING_SITE_URL = process.env.STAGING_SITE_URL;
const STAGING_SITE_AUTH_TOKEN = process.env.STAGING_SITE_AUTH_TOKEN;
const LIVE_SITE_URL = process.env.LIVE_SITE_URL;
const LIVE_SITE_AUTH_TOKEN = process.env.LIVE_SITE_AUTH_TOKEN;

const REPORT_PATH = path.join(process.cwd(), 'wp-content', 'themes', 'activation-states.json');

async function makeApiRequest(url, options) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                ...options.headers,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        logger.error(`API request failed: ${url}`, {
            error: error.message,
            stack: error.stack
        });
        throw error;
    }
}

async function getThemeStates(siteUrl, authToken) {
    try {
        const result = await makeApiRequest(
            `${siteUrl}/wp-json/techops/v1/themes/list`,
            {
                method: 'GET',
                headers: {
                    'Authorization': `Basic ${authToken}`
                }
            }
        );

        if (!result.themes || !Array.isArray(result.themes)) {
            throw new Error('Invalid theme list response format');
        }

        return result.themes;
    } catch (error) {
        logger.error(`Failed to get theme states from ${siteUrl}`, {
            error: error.message,
            stack: error.stack
        });
        throw error;
    }
}

async function activateTheme(themeSlug) {
    try {
        logger.info(`Attempting to activate theme: ${themeSlug}`);
        const result = await makeApiRequest(
            `${STAGING_SITE_URL}/wp-json/techops/v1/themes/activate/${themeSlug}`,
            {
                method: 'POST',
                headers: {
                    'Authorization': `Basic ${STAGING_SITE_AUTH_TOKEN}`,
                    'Content-Type': 'application/json'
                }
            }
        );

        if (!result.success) {
            throw new Error(`Failed to activate theme: ${themeSlug}`);
        }

        logger.info(`Successfully activated theme: ${themeSlug}`);
        return true;
    } catch (error) {
        logger.error(`Theme activation failed: ${themeSlug}`, {
            error: error.message,
            stack: error.stack
        });
        throw error;
    }
}

async function writeReport(report) {
    try {
        const reportDir = path.dirname(REPORT_PATH);
        if (!fs.existsSync(reportDir)) {
            fs.mkdirSync(reportDir, { recursive: true });
        }

        fs.writeFileSync(REPORT_PATH, JSON.stringify(report, null, 2));
        logger.info(`Theme activation report written to ${REPORT_PATH}`);
    } catch (error) {
        logger.error('Failed to write theme activation report', {
            error: error.message,
            stack: error.stack
        });
        throw error;
    }
}

async function syncThemeActivation() {
    try {
        logger.info('Starting theme activation sync');

        // Get theme states from both sites
        const liveThemes = await getThemeStates(LIVE_SITE_URL, LIVE_SITE_AUTH_TOKEN);
        const stagingThemes = await getThemeStates(STAGING_SITE_URL, STAGING_SITE_AUTH_TOKEN);

        const changes = {
            activated: [],
            errors: [],
            skipped: [],
            timestamp: new Date().toISOString()
        };

        // Find active theme in live site
        const activeTheme = liveThemes.find(theme => theme.active);
        if (!activeTheme) {
            throw new Error('No active theme found in live site');
        }

        // Check if theme needs activation in staging
        const stagingTheme = stagingThemes.find(theme => theme.slug === activeTheme.slug);
        if (!stagingTheme) {
            logger.warn(`Theme ${activeTheme.slug} not found in staging site`);
            changes.skipped.push({
                slug: activeTheme.slug,
                reason: 'Theme not found in staging site'
            });
        } else if (!stagingTheme.active) {
            try {
                await activateTheme(activeTheme.slug);
                changes.activated.push(activeTheme.slug);
            } catch (error) {
                changes.errors.push({
                    slug: activeTheme.slug,
                    error: error.message
                });
            }
        } else {
            logger.info(`Theme ${activeTheme.slug} already active in staging site`);
            changes.skipped.push({
                slug: activeTheme.slug,
                reason: 'Already active'
            });
        }

        // Generate report
        const report = {
            themes: [{
                slug: activeTheme.slug,
                active: true,
                last_sync: changes.timestamp,
                sync_status: changes.errors.length === 0 ? 'success' : 'error'
            }],
            meta: {
                last_sync_timestamp: changes.timestamp,
                sync_version: '1.0',
                changes: changes
            }
        };

        await writeReport(report);

        if (changes.errors.length > 0) {
            throw new Error(`Theme sync completed with ${changes.errors.length} errors`);
        }

        logger.info('Theme activation sync completed successfully', {
            activated: changes.activated.length,
            skipped: changes.skipped.length
        });

        return changes;
    } catch (error) {
        logger.error('Theme activation sync failed', {
            error: error.message,
            stack: error.stack
        });
        throw error;
    }
}

// Execute if run directly
if (require.main === module) {
    syncThemeActivation().catch(error => {
        console.error('Fatal error:', error);
        process.exit(1);
    });
}

module.exports = syncThemeActivation; 