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
const MAX_RETRIES = 3;
const RETRY_DELAY = 2000; // 2 seconds

// Validate environment variables
function validateEnvironment() {
    const requiredVars = {
        STAGING_SITE_URL,
        STAGING_SITE_AUTH_TOKEN,
        LIVE_SITE_URL,
        LIVE_SITE_AUTH_TOKEN
    };

    const missingVars = Object.entries(requiredVars)
        .filter(([, value]) => !value)
        .map(([key]) => key);

    if (missingVars.length > 0) {
        throw new Error(`Missing required environment variables: ${missingVars.join(', ')}`);
    }

    // Validate URL formats
    try {
        new URL(STAGING_SITE_URL);
        new URL(LIVE_SITE_URL);
    } catch (error) {
        throw new Error(`Invalid URL format: ${error.message}`);
    }
}

// Helper function for delay
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

async function makeApiRequest(url, options, retryCount = 0) {
    try {
        logger.info('Making API request', { 
            url,
            method: options.method || 'GET',
            attempt: retryCount + 1
        });

        const response = await fetch(url, {
            ...options,
            headers: {
                ...options.headers,
                'Accept': 'application/json',
                'User-Agent': 'TechOps-Theme-Sync/1.0'
            }
        });

        const responseText = await response.text();
        let responseData;

        try {
            responseData = JSON.parse(responseText);
        } catch (parseError) {
            logger.error('Failed to parse response as JSON', {
                url,
                responseText,
                error: parseError.message
            });
            
            if (retryCount < MAX_RETRIES) {
                logger.info(`Retrying request (${retryCount + 1}/${MAX_RETRIES})...`);
                await delay(RETRY_DELAY * Math.pow(2, retryCount));
                return makeApiRequest(url, options, retryCount + 1);
            }
            throw new Error('Invalid JSON response from API');
        }

        if (!response.ok) {
            const errorDetails = {
                status: response.status,
                statusText: response.statusText,
                body: responseData,
                url,
                method: options.method || 'GET'
            };
            logger.error('API request failed', errorDetails);

            if (retryCount < MAX_RETRIES) {
                logger.info(`Retrying request (${retryCount + 1}/${MAX_RETRIES})...`);
                await delay(RETRY_DELAY * Math.pow(2, retryCount));
                return makeApiRequest(url, options, retryCount + 1);
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return responseData;
    } catch (error) {
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            logger.error('Network error', {
                url,
                error: error.message
            });
            if (retryCount < MAX_RETRIES) {
                logger.info(`Retrying request (${retryCount + 1}/${MAX_RETRIES})...`);
                await delay(RETRY_DELAY * Math.pow(2, retryCount));
                return makeApiRequest(url, options, retryCount + 1);
            }
        }
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

        // Handle different response formats
        let themes;
        if (Array.isArray(result)) {
            themes = result;
        } else if (result.themes && Array.isArray(result.themes)) {
            themes = result.themes;
        } else if (typeof result === 'object') {
            // Try to extract theme information from object
            themes = Object.entries(result)
                .filter(([, value]) => typeof value === 'object' && value.stylesheet)
                .map(([slug, data]) => ({
                    slug,
                    active: data.active || false,
                    name: data.name || slug
                }));
        }

        if (!themes || themes.length === 0) {
            logger.warn('No themes found in response', { siteUrl, responseData: result });
            return [];
        }

        return themes;
    } catch (error) {
        logger.error(`Failed to get theme states from ${siteUrl}`, {
            error: error.message,
            stack: error.stack
        });
        return [];
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
                },
                body: JSON.stringify({ theme: themeSlug })
            }
        );

        if (!result.success && result.status !== 'active') {
            throw new Error(`Failed to activate theme: ${themeSlug} - ${result.message || 'Unknown error'}`);
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

async function ensureReportDirectory() {
    const reportDir = path.dirname(REPORT_PATH);
    try {
        if (!fs.existsSync(reportDir)) {
            fs.mkdirSync(reportDir, { recursive: true });
            logger.info(`Created report directory: ${reportDir}`);
        }
        return true;
    } catch (error) {
        logger.error('Failed to create report directory', {
            dir: reportDir,
            error: error.message
        });
        return false;
    }
}

async function writeReport(report) {
    try {
        if (!await ensureReportDirectory()) {
            throw new Error('Failed to ensure report directory exists');
        }

        fs.writeFileSync(REPORT_PATH, JSON.stringify(report, null, 2));
        logger.info(`Theme activation report written to ${REPORT_PATH}`);
        return true;
    } catch (error) {
        logger.error('Failed to write theme activation report', {
            error: error.message,
            stack: error.stack
        });
        return false;
    }
}

async function syncThemeActivation() {
    try {
        logger.info('Starting theme activation sync');
        validateEnvironment();

        const changes = {
            activated: [],
            errors: [],
            skipped: [],
            timestamp: new Date().toISOString()
        };

        // Get theme states from both sites
        const [liveThemes, stagingThemes] = await Promise.all([
            getThemeStates(LIVE_SITE_URL, LIVE_SITE_AUTH_TOKEN),
            getThemeStates(STAGING_SITE_URL, STAGING_SITE_AUTH_TOKEN)
        ]);

        if (liveThemes.length === 0) {
            logger.warn('No themes found in live site, creating fallback report');
            const fallbackReport = {
                themes: [],
                meta: {
                    last_sync_timestamp: changes.timestamp,
                    sync_version: '1.0',
                    status: 'warning',
                    message: 'No themes found in live site'
                }
            };
            await writeReport(fallbackReport);
            return changes;
        }

        // Find active theme in live site
        const activeTheme = liveThemes.find(theme => theme.active);
        if (!activeTheme) {
            logger.warn('No active theme found in live site, using first available theme');
            activeTheme = liveThemes[0];
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

        const reportWritten = await writeReport(report);
        if (!reportWritten) {
            logger.warn('Failed to write report, but sync process completed');
        }

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

        // Try to write error report
        const errorReport = {
            themes: [],
            meta: {
                last_sync_timestamp: new Date().toISOString(),
                sync_version: '1.0',
                status: 'error',
                error: error.message
            }
        };
        await writeReport(errorReport).catch(() => {});

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