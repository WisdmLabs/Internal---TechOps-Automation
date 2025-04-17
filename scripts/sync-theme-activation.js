#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const axios = require('axios');
const Logger = require('./utils/logger');

const logger = new Logger('theme-activation-sync');
const BASE_DIR = path.join(__dirname, '..', 'wp-content', 'themes');
const ACTIVATION_STATES_FILE = path.join(BASE_DIR, 'theme-activation-states.json');

// Validate required environment variables
function validateEnvironment() {
  const required = ['STAGING_SITE_AUTH_TOKEN', 'STAGING_SITE_URL'];
  const missing = required.filter(key => !process.env[key]);
  
  if (missing.length > 0) {
    logger.error(`Missing required environment variables: ${missing.join(', ')}`);
    process.exit(1);
  }
}

// Make API request with error handling
async function makeApiRequest(method, endpoint, data = null) {
  const retries = 3;
  for (let attempt = 1; attempt <= retries; attempt++) {
    try {
      logger.info(`Making API request (Attempt ${attempt}/${retries})`, { 
        method,
        endpoint
      });

      const response = await axios({
        method,
        url: `${process.env.STAGING_SITE_URL}/wp-json/techops/v1/themes/${endpoint}`,
        headers: {
          'Authorization': `Basic ${process.env.STAGING_SITE_AUTH_TOKEN}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        data
      });
      return response.data;
    } catch (error) {
      logger.error(`API request failed (Attempt ${attempt}/${retries}): ${error.message}`);
      if (error.response) {
        logger.error(`Status: ${error.response.status}`);
        logger.error(`Response: ${JSON.stringify(error.response.data)}`);
      }
      
      if (attempt === retries) {
        throw error;
      }
      
      // Exponential backoff
      await new Promise(resolve => setTimeout(resolve, Math.pow(2, attempt) * 1000));
    }
  }
}

// Get current theme states
async function getCurrentThemeStates() {
  logger.info('Fetching current theme states');
  try {
    const themes = await makeApiRequest('GET', 'list');
    return themes.reduce((acc, theme) => {
      acc[theme.slug] = theme.active;
      return acc;
    }, {});
  } catch (error) {
    logger.error('Failed to fetch current theme states');
    throw error;
  }
}

// Activate a theme
async function activateTheme(themeSlug) {
  logger.info(`Activating theme: ${themeSlug}`);
  try {
    const result = await makeApiRequest('POST', `activate/${themeSlug}`, { theme: themeSlug });
    if (!result.success) {
      throw new Error('Theme activation failed');
    }
    logger.info(`Successfully activated theme: ${themeSlug}`);
    return true;
  } catch (error) {
    logger.error(`Failed to activate theme ${themeSlug}: ${error.message}`);
    return false;
  }
}

// Deactivate a theme
async function deactivateTheme(themeSlug) {
  logger.info(`Deactivating theme: ${themeSlug}`);
  try {
    const result = await makeApiRequest('POST', `deactivate/${themeSlug}`, { theme: themeSlug });
    if (!result.success) {
      throw new Error('Theme deactivation failed');
    }
    logger.info(`Successfully deactivated theme: ${themeSlug}`);
    return true;
  } catch (error) {
    logger.error(`Failed to deactivate theme ${themeSlug}: ${error.message}`);
    return false;
  }
}

// Main sync function
async function syncThemeStates() {
  try {
    validateEnvironment();
    
    // Check if activation states file exists
    if (!fs.existsSync(ACTIVATION_STATES_FILE)) {
      logger.error(`Theme activation states file not found at ${ACTIVATION_STATES_FILE}`);
      process.exit(1);
    }
    
    // Read desired states
    const desiredStates = JSON.parse(fs.readFileSync(ACTIVATION_STATES_FILE, 'utf8'));
    logger.info(`Loaded ${Object.keys(desiredStates).length} desired theme states`);
    
    // Get current states
    const currentStates = await getCurrentThemeStates();
    logger.info(`Retrieved ${Object.keys(currentStates).length} current theme states`);
    
    // Initialize results
    const results = {
      activated: 0,
      deactivated: 0,
      skipped: 0,
      errors: 0,
      details: []
    };
    
    // Sync each theme
    for (const [themeSlug, shouldBeActive] of Object.entries(desiredStates)) {
      const isCurrentlyActive = currentStates[themeSlug] || false;
      
      if (shouldBeActive === isCurrentlyActive) {
        logger.info(`Theme ${themeSlug} already in desired state`);
        results.skipped++;
        results.details.push({
          slug: themeSlug,
          action: 'skipped',
          reason: 'Already in desired state'
        });
        continue;
      }
      
      try {
        if (shouldBeActive) {
          const success = await activateTheme(themeSlug);
          if (success) {
            results.activated++;
            results.details.push({
              slug: themeSlug,
              action: 'activated',
              success: true
            });
          } else {
            results.errors++;
            results.details.push({
              slug: themeSlug,
              action: 'activated',
              success: false,
              error: 'Activation failed'
            });
          }
        } else {
          const success = await deactivateTheme(themeSlug);
          if (success) {
            results.deactivated++;
            results.details.push({
              slug: themeSlug,
              action: 'deactivated',
              success: true
            });
          } else {
            results.errors++;
            results.details.push({
              slug: themeSlug,
              action: 'deactivated',
              success: false,
              error: 'Deactivation failed'
            });
          }
        }
      } catch (error) {
        logger.error(`Error processing theme ${themeSlug}: ${error.message}`);
        results.errors++;
        results.details.push({
          slug: themeSlug,
          action: shouldBeActive ? 'activated' : 'deactivated',
          success: false,
          error: error.message
        });
      }
    }
    
    // Write sync report
    const report = {
      timestamp: new Date().toISOString(),
      results,
      sync_status: results.errors > 0 ? 'error' : 'success'
    };
    
    fs.writeFileSync(
      path.join(BASE_DIR, 'theme-sync-report.json'),
      JSON.stringify(report, null, 2)
    );
    
    logger.info('Theme sync completed', report);
    
    if (results.errors > 0) {
      process.exit(1);
    }
  } catch (error) {
    logger.error('Theme sync failed', error);
    process.exit(1);
  }
}

// Run the sync
syncThemeStates(); 