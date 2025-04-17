#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const Logger = require('./utils/logger');

// Initialize logger
const logger = new Logger('ProcessThemes');

// Constants
const BASE_DIR = path.join(process.cwd(), 'wp-content', 'themes');
const THEME_SLUG = process.env.THEME_SLUG || '';

// Verify required environment variables
if (!process.env.LIVE_SITE_AUTH_TOKEN || !process.env.LIVE_SITE_URL) {
    logger.error('Missing required environment variables: LIVE_SITE_AUTH_TOKEN or LIVE_SITE_URL');
    process.exit(1);
}

async function processThemes(themesList) {
    try {
        // Ensure the themes directory exists
        if (!fs.existsSync(BASE_DIR)) {
            fs.mkdirSync(BASE_DIR, { recursive: true });
        }

        // Filter themes if a specific theme slug is provided
        const themesToProcess = THEME_SLUG
            ? themesList.filter(theme => theme.slug === THEME_SLUG)
            : themesList;

        logger.info(`Processing ${themesToProcess.length} themes...`);

        // Create activation states object
        const activationStates = {};
        
        // Process each theme and record its activation state
        for (const theme of themesToProcess) {
            try {
                const themeDir = path.join(BASE_DIR, theme.slug);
                const zipPath = path.join(BASE_DIR, `${theme.slug}.zip`);

                logger.info(`\nProcessing theme: ${theme.slug}`);
                logger.info(`Theme directory path: ${themeDir}`);
                logger.info(`Theme zip path: ${zipPath}`);

                // Record activation state
                activationStates[theme.slug] = theme.active || false;
                logger.info(`Theme ${theme.slug} activation state: ${theme.active ? 'active' : 'inactive'}`);

                // Remove existing theme directory if it exists
                if (fs.existsSync(themeDir)) {
                    logger.info(`Removing existing ${theme.slug} directory...`);
                    fs.rmSync(themeDir, { recursive: true, force: true });
                }

                // Download theme ZIP directly to themes directory
                logger.info(`Downloading ${theme.slug}...`);
                execSync(
                    `curl -H "Authorization: Basic ${process.env.LIVE_SITE_AUTH_TOKEN}" ` +
                    `"${process.env.LIVE_SITE_URL}/wp-json/techops/v1/themes/download/${theme.slug}" ` +
                    `--output "${zipPath}" --fail --silent --show-error`
                );

                // Verify download
                if (!fs.existsSync(zipPath)) {
                    throw new Error('Theme download failed - file not created');
                }

                const fileSize = fs.statSync(zipPath).size;
                if (fileSize === 0) {
                    throw new Error('Theme download failed - file is empty');
                }

                // Verify ZIP file integrity
                try {
                    execSync(`unzip -t "${zipPath}" > /dev/null`);
                } catch (error) {
                    throw new Error('Invalid ZIP file downloaded');
                }

                // Extract ZIP directly to theme directory
                logger.info(`Extracting ${theme.slug}...`);
                execSync(`unzip -q -o "${zipPath}" -d "${themeDir}"`);

                // Remove the ZIP file
                fs.unlinkSync(zipPath);

                // Verify theme directory contents
                logger.info(`Verifying ${theme.slug} contents...`);
                const themeFiles = fs.readdirSync(themeDir);
                if (themeFiles.length === 0) {
                    throw new Error('Theme directory is empty after extraction');
                }
                logger.info(`Theme directory contains ${themeFiles.length} files/directories`);

                logger.info(`✅ Successfully processed ${theme.slug}`);
            } catch (error) {
                logger.error(`Error processing theme ${theme.slug}:`, { error: error.message });
                // The shell script will handle restoration from backup
            }
        }

        // Write activation states to file
        const activationStatesPath = path.join(BASE_DIR, 'theme-activation-states.json');
        fs.writeFileSync(activationStatesPath, JSON.stringify(activationStates, null, 2));
        logger.info(`Created theme activation states file at ${activationStatesPath}`);

        logger.info('\n✅ Theme processing completed successfully');
    } catch (error) {
        logger.error('Error processing themes:', { error: error.message });
        process.exit(1);
    }
}

// Export the main function
module.exports = { processThemes };

// Read themes list from stdin
const themesList = JSON.parse(fs.readFileSync(0, 'utf-8'));
processThemes(themesList).catch(error => {
    console.error('❌ Fatal error:', error.message);
    process.exit(1);
});
