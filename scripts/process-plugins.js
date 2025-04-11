#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const BackupManager = require('./backup-manager');
const DependencyChecker = require('./dependency-checker');
const Logger = require('./logger');

const BASE_DIR = 'wp-content/plugins';
const EXCLUDED_PLUGINS = ['techops-content-sync'];

// Verify required environment variables
const requiredEnvVars = [
    'LIVE_SITE_AUTH_TOKEN',
    'LIVE_SITE_URL'
];

for (const envVar of requiredEnvVars) {
    if (!process.env[envVar]) {
        console.error(`Error: Required environment variable ${envVar} is not set`);
        process.exit(1);
    }
}

// Initialize utilities
const backupManager = new BackupManager(BASE_DIR);
const dependencyChecker = new DependencyChecker(process.env.LIVE_SITE_URL, process.env.LIVE_SITE_AUTH_TOKEN);
const logger = new Logger(path.join(BASE_DIR, 'sync.log'));

async function processPlugins(pluginsList) {
    try {
        // Ensure the plugins directory exists
        if (!fs.existsSync(BASE_DIR)) {
            fs.mkdirSync(BASE_DIR, { recursive: true });
        }

        logger.info(`Processing ${pluginsList.length} plugins...`);

        // Create initial backup
        const backupPath = await backupManager.createBackup();
        logger.info('Created initial backup', { backupPath });

        // Create activation states file
        const activationStates = {
            plugins: pluginsList.map(plugin => ({
                slug: plugin.slug,
                active: plugin.active,
                version: plugin.version,
                name: plugin.name
            })),
            lastSync: new Date().toISOString()
        };

        // Save activation states
        fs.writeFileSync(
            path.join(BASE_DIR, 'activation-states.json'),
            JSON.stringify(activationStates, null, 2)
        );

        // Process each plugin
        for (const plugin of pluginsList) {
            try {
                // Skip excluded plugins
                if (EXCLUDED_PLUGINS.includes(plugin.slug)) {
                    logger.info(`Skipping excluded plugin: ${plugin.slug}`);
                    continue;
                }

                // Check dependencies
                const dependencies = await dependencyChecker.checkDependencies(plugin);
                if (!dependencies.areMet) {
                    logger.warn(`Skipping ${plugin.slug}: Missing dependencies`, {
                        missing: dependencies.missing,
                        details: dependencies.details
                    });
                    continue;
                }

                const pluginDir = path.join(BASE_DIR, plugin.slug);
                const zipPath = path.join(BASE_DIR, `${plugin.slug}.zip`);
                const tempDir = path.join(BASE_DIR, '_temp_extract');

                logger.info(`Processing plugin: ${plugin.slug}`);

                // Create backup point for this plugin
                const pluginBackupPath = await backupManager.createBackup();
                logger.debug(`Created backup point for ${plugin.slug}`, { backupPath: pluginBackupPath });

                // Download plugin ZIP
                logger.info(`Downloading ${plugin.slug}...`);
                execSync(
                    `curl -H "Authorization: Basic ${process.env.LIVE_SITE_AUTH_TOKEN}" ` +
                    `"${process.env.LIVE_SITE_URL}/wp-json/techops/v1/plugins/download/${plugin.slug}" ` +
                    `--output "${zipPath}" --fail --silent --show-error`
                );

                // Create temp directory for extraction
                if (!fs.existsSync(tempDir)) {
                    fs.mkdirSync(tempDir, { recursive: true });
                }

                // Extract ZIP to temp directory with force overwrite
                logger.info(`Extracting ${plugin.slug}...`);
                execSync(`unzip -o -q "${zipPath}" -d "${tempDir}"`);

                // Remove existing plugin directory if it exists
                if (fs.existsSync(pluginDir)) {
                    logger.info(`Removing existing ${plugin.slug} directory...`);
                    fs.rmSync(pluginDir, { recursive: true, force: true });
                }

                // Move from temp directory to final location
                logger.info(`Moving ${plugin.slug} to final location...`);
                fs.renameSync(path.join(tempDir, plugin.slug), pluginDir);

                // Clean up
                logger.info(`Cleaning up temporary files for ${plugin.slug}...`);
                fs.unlinkSync(zipPath);
                fs.rmSync(tempDir, { recursive: true, force: true });

                logger.info(`Successfully processed ${plugin.slug}`);
            } catch (error) {
                logger.error(`Error processing plugin ${plugin.slug}:`, error);
                throw error;
            }
        }

        logger.info('Plugin processing completed successfully');
    } catch (error) {
        logger.error('Error in plugin processing:', error);
        throw error;
    }
}

// Read plugins list from stdin
let data = '';
process.stdin.on('data', chunk => {
    data += chunk;
});

process.stdin.on('end', async () => {
    try {
        const pluginsList = JSON.parse(data);
        await processPlugins(pluginsList);
    } catch (error) {
        console.error('Error:', error.message);
        process.exit(1);
    }
}); 