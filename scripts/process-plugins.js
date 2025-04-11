#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const BackupManager = require('./backup-manager');
const DependencyChecker = require('./dependency-checker');
const Logger = require('./logger');

const BASE_DIR = 'wp-content/plugins';
const EXCLUDED_PLUGINS = ['techops-content-sync'];

// Initialize utilities
const backupManager = new BackupManager(BASE_DIR);
const dependencyChecker = new DependencyChecker(process.env.SITE_URL, process.env.WP_AUTH_TOKEN);
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
                    `curl -H "Authorization: Basic ${process.env.WP_AUTH_TOKEN}" ` +
                    `"${process.env.SITE_URL}/wp-json/techops/v1/plugins/download/${plugin.slug}" ` +
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
                const extractedFiles = fs.readdirSync(tempDir);
                if (extractedFiles.length === 1 && fs.statSync(path.join(tempDir, extractedFiles[0])).isDirectory()) {
                    // If extracted to a single directory, move that directory
                    fs.renameSync(path.join(tempDir, extractedFiles[0]), pluginDir);
                } else {
                    // If extracted multiple files, move them all to a new directory
                    fs.mkdirSync(pluginDir, { recursive: true });
                    extractedFiles.forEach(file => {
                        fs.renameSync(path.join(tempDir, file), path.join(pluginDir, file));
                    });
                }

                // Clean up
                logger.info(`Cleaning up ${plugin.slug}...`);
                fs.rmSync(zipPath);
                fs.rmSync(tempDir, { recursive: true, force: true });

                logger.info(`Successfully processed ${plugin.slug}`);
            } catch (error) {
                logger.error(`Error processing plugin ${plugin.slug}:`, { error: error.message });
                // Attempt to restore from backup
                try {
                    await backupManager.restoreBackup(pluginBackupPath);
                    logger.info(`Restored ${plugin.slug} from backup`);
                } catch (restoreError) {
                    logger.error(`Failed to restore ${plugin.slug} from backup:`, { error: restoreError.message });
                }
            }
        }

        logger.info('Plugin processing completed');
    } catch (error) {
        logger.error('Error in plugin processing:', { error: error.message });
        process.exit(1);
    }
}

// Read plugins list from stdin
let data = '';
process.stdin.on('data', chunk => {
    data += chunk;
});

process.stdin.on('end', () => {
    try {
        const pluginsList = JSON.parse(data);
        processPlugins(pluginsList).catch(error => {
            logger.error('Error:', { error: error.message });
            process.exit(1);
        });
    } catch (error) {
        logger.error('Error parsing plugins list:', { error: error.message });
        process.exit(1);
    }
}); 