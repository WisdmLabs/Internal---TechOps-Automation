#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const Logger = require('./utils/logger');
const DependencyChecker = require('./utils/dependency-checker');

// Initialize logger
const logger = new Logger('ProcessPlugins');

// Constants
const BASE_DIR = path.join(process.cwd(), 'wp-content', 'plugins');
const PLUGIN_SLUG = process.env.PLUGIN_SLUG || '';

// Verify required environment variables
if (!process.env.LIVE_SITE_AUTH_TOKEN || !process.env.LIVE_SITE_URL) {
    logger.error('Missing required environment variables: LIVE_SITE_AUTH_TOKEN or LIVE_SITE_URL');
    process.exit(1);
}

async function processPlugins(pluginsList) {
    try {
        // Ensure the plugins directory exists
        if (!fs.existsSync(BASE_DIR)) {
            fs.mkdirSync(BASE_DIR, { recursive: true });
        }

        // Filter plugins if a specific plugin slug is provided
        const pluginsToProcess = PLUGIN_SLUG
            ? pluginsList.filter(plugin => plugin.slug === PLUGIN_SLUG)
            : pluginsList;

        logger.info(`Processing ${pluginsToProcess.length} plugins...`);

        // Initialize dependency checker
        const dependencyChecker = new DependencyChecker();

        for (const plugin of pluginsToProcess) {
            try {
                const pluginDir = path.join(BASE_DIR, plugin.slug);
                const zipPath = path.join(BASE_DIR, `${plugin.slug}.zip`);

                logger.info(`\nProcessing plugin: ${plugin.slug}`);
                logger.info(`Plugin directory path: ${pluginDir}`);
                logger.info(`Plugin zip path: ${zipPath}`);

                // Check dependencies
                const dependencies = await dependencyChecker.checkDependencies(
                    plugin,
                    process.env.LIVE_SITE_URL,
                    process.env.LIVE_SITE_AUTH_TOKEN
                );

                if (!dependencies.areMet) {
                    logger.warn(`Plugin ${plugin.slug} dependencies not met:`, {
                        missing: dependencies.missing,
                        current: {
                            wordpress: dependencies.wordpress,
                            php: dependencies.php
                        }
                    });
                    // Continue processing despite dependency warnings
                }

                // Remove existing plugin directory if it exists
                if (fs.existsSync(pluginDir)) {
                    logger.info(`Removing existing ${plugin.slug} directory...`);
                    fs.rmSync(pluginDir, { recursive: true, force: true });
                }

                // Download plugin ZIP directly to plugins directory
                logger.info(`Downloading ${plugin.slug}...`);
                execSync(
                    `curl -H "Authorization: Basic ${process.env.LIVE_SITE_AUTH_TOKEN}" ` +
                    `"${process.env.LIVE_SITE_URL}/wp-json/techops/v1/plugins/download/${plugin.slug}" ` +
                    `--output "${zipPath}" --fail --silent --show-error`
                );

                // Verify download
                if (!fs.existsSync(zipPath)) {
                    throw new Error('Plugin download failed - file not created');
                }

                const fileSize = fs.statSync(zipPath).size;
                if (fileSize === 0) {
                    throw new Error('Plugin download failed - file is empty');
                }

                // Verify ZIP file integrity
                try {
                    execSync(`unzip -t "${zipPath}" > /dev/null`);
                } catch (error) {
                    throw new Error('Invalid ZIP file downloaded');
                }

                // Extract ZIP directly to plugin directory
                logger.info(`Extracting ${plugin.slug}...`);
                execSync(`unzip -q -o "${zipPath}" -d "${pluginDir}"`);

                // Remove the ZIP file
                fs.unlinkSync(zipPath);

                // Verify plugin directory contents
                logger.info(`Verifying ${plugin.slug} contents...`);
                const pluginFiles = fs.readdirSync(pluginDir);
                if (pluginFiles.length === 0) {
                    throw new Error('Plugin directory is empty after extraction');
                }
                logger.info(`Plugin directory contains ${pluginFiles.length} files/directories`);

                // Save activation state
                const activationStatesPath = path.join(BASE_DIR, 'activation-states.json');
                let activationStates = { plugins: [] };
                
                if (fs.existsSync(activationStatesPath)) {
                    try {
                        activationStates = JSON.parse(fs.readFileSync(activationStatesPath, 'utf8'));
                        // Remove existing entry for this plugin if it exists
                        activationStates.plugins = activationStates.plugins.filter(p => p.slug !== plugin.slug);
                    } catch (error) {
                        logger.warn(`Error reading activation states: ${error.message}`);
                    }
                }
                
                // Add new activation state
                activationStates.plugins.push({
                    slug: plugin.slug,
                    active: plugin.active || false
                });
                
                // Write updated activation states
                fs.writeFileSync(activationStatesPath, JSON.stringify(activationStates, null, 2));
                logger.info(`Updated activation state for ${plugin.slug}`);

                logger.info(`✅ Successfully processed ${plugin.slug}`);
            } catch (error) {
                logger.error(`Error processing plugin ${plugin.slug}:`, { error: error.message });
                // The shell script will handle restoration from backup
            }
        }

        logger.info('\n✅ Plugin processing completed successfully');
    } catch (error) {
        logger.error('Error processing plugins:', { error: error.message });
        process.exit(1);
    }
}

// Export the main function
module.exports = { processPlugins };

// Read plugins list from stdin
const pluginsList = JSON.parse(fs.readFileSync(0, 'utf-8'));
processPlugins(pluginsList).catch(error => {
    console.error('❌ Fatal error:', error.message);
    process.exit(1);
});
