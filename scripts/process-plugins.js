#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const BackupManager = require('./utils/backup-manager');
const DependencyChecker = require('./utils/dependency-checker');
const Logger = require('./utils/logger');

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

// Configuration
const BASE_DIR = path.join(__dirname, '..');
const EXCLUDED_PLUGINS = ['techops-content-sync'];
const LOG_FILE = path.join(BASE_DIR, 'plugin-sync.log');

// Initialize utilities
const backupManager = new BackupManager(path.join(BASE_DIR, 'wp-content/plugins'));
const dependencyChecker = new DependencyChecker();
const logger = new Logger(LOG_FILE);

// Function to make API request with retries
async function makeApiRequest(url, token, method = 'GET', data = null) {
    const maxRetries = 3;
    let retryCount = 0;
    
    while (retryCount < maxRetries) {
        try {
            const command = [
                'curl -s',
                `-H "Authorization: Basic ${token}"`,
                '-H "Content-Type: application/json"',
                '-H "Accept: application/json"',
                '--max-time 30'
            ];
            
            if (method === 'POST') {
                command.push('-X POST');
                if (data) {
                    command.push(`-d '${JSON.stringify(data)}'`);
                }
            }
            
            command.push(`"${url}"`);
            
            const response = execSync(command.join(' ')).toString();
            return JSON.parse(response);
        } catch (error) {
            retryCount++;
            if (retryCount === maxRetries) {
                throw error;
            }
            console.log(`Request failed, retrying in 5 seconds... (Attempt ${retryCount} of ${maxRetries})`);
            await new Promise(resolve => setTimeout(resolve, 5000));
        }
    }
}

// Function to validate plugin metadata
function validatePluginMetadata(plugin) {
    const requiredFields = ['slug', 'name', 'version'];
    for (const field of requiredFields) {
        if (!plugin[field]) {
            throw new Error(`Missing required field: ${field}`);
        }
    }
    
    if (!/^[a-z0-9-_.]+$/.test(plugin.slug)) {
        throw new Error('Invalid plugin slug format');
    }
    
    return true;
}

async function processPlugin(plugin) {
    try {
        // Skip excluded plugins
        if (EXCLUDED_PLUGINS.includes(plugin.slug)) {
            logger.info(`Skipping excluded plugin: ${plugin.slug}`);
            return true;
        }

        // Validate plugin slug
        if (!/^[a-z0-9-_.]+$/.test(plugin.slug)) {
            throw new Error('Invalid plugin slug format');
        }

        // Check dependencies
        const dependencies = await dependencyChecker.checkDependencies(
            plugin,
            process.env.STAGING_SITE_URL,
            process.env.STAGING_SITE_AUTH_TOKEN
        );

        if (!dependencies.areMet) {
            logger.warn(`Plugin ${plugin.slug} has unmet dependencies`, { dependencies });
            // Continue processing despite dependencies warning
        }

        // Create backup
        const backupPath = await backupManager.createBackup(plugin.slug);

        try {
            // Download plugin
            const pluginUrl = `${process.env.LIVE_SITE_URL}/wp-json/techops/v1/plugins/download/${plugin.slug}`;
            const zipPath = path.join(BASE_DIR, 'wp-content/plugins', `${plugin.slug}.zip`);
            
            // Download with proper headers and error handling
            execSync(`curl -s -L -H "Authorization: Basic ${process.env.LIVE_SITE_AUTH_TOKEN}" "${pluginUrl}" -o "${zipPath}" --fail`);
            
            // Verify download
            if (!fs.existsSync(zipPath) || fs.statSync(zipPath).size === 0) {
                throw new Error('Plugin download failed or file is empty');
            }

            // Extract plugin
            const pluginDir = path.join(BASE_DIR, 'wp-content/plugins', plugin.slug);
            execSync(`unzip -q -o "${zipPath}" -d "${pluginDir}"`);
            
            // Clean up
            fs.unlinkSync(zipPath);
            
            return true;
        } catch (error) {
            // Restore from backup if available
            if (backupPath) {
                await backupManager.restoreBackup(backupPath);
            }
            throw error;
        }
    } catch (error) {
        logger.error(`Failed to process plugin ${plugin.slug}`, { error: error.message });
        return false;
    }
}

async function processPlugins() {
    let backupPath = null;
    try {
        logger.info('Starting plugin processing...');
        
        // Ensure base directory exists
        if (!fs.existsSync(BASE_DIR)) {
            logger.info(`Creating directory: ${BASE_DIR}`);
            fs.mkdirSync(BASE_DIR, { recursive: true });
        }
        
        // Create backup
        backupPath = await backupManager.createBackup();
        logger.info(`Created backup at: ${backupPath}`);
        
        // Get plugins list
        logger.info('Fetching plugins list...');
        let pluginsList;
        try {
            pluginsList = await makeApiRequest(
                `${process.env.LIVE_SITE_URL}/wp-json/techops/v1/plugins/list`,
                process.env.LIVE_SITE_AUTH_TOKEN
            );
            if (!Array.isArray(pluginsList)) {
                throw new Error('Invalid response format from plugins list endpoint');
            }
        } catch (error) {
            logger.error('Failed to fetch plugins list', { error: error.message });
            throw error;
        }
        
        logger.info(`Found ${pluginsList.length} plugins to process`);
        
        // Filter out excluded plugins
        const filteredPlugins = pluginsList.filter(plugin => !EXCLUDED_PLUGINS.includes(plugin.slug));
        console.log(`Processing ${filteredPlugins.length} plugins (excluding: ${EXCLUDED_PLUGINS.join(', ')})`);
        
        // Create activation states file
        const activationStates = {
            plugins: filteredPlugins.map(plugin => ({
                slug: plugin.slug,
                active: plugin.active,
                version: plugin.version,
                name: plugin.name
            })),
            lastSync: new Date().toISOString()
        };
        
        // Ensure directory exists before writing file
        const activationStatesPath = path.join(BASE_DIR, 'activation-states.json');
        fs.writeFileSync(activationStatesPath, JSON.stringify(activationStates, null, 2));
        logger.info(`Created activation states file at: ${activationStatesPath}`);
        
        // Process each plugin
        for (const plugin of filteredPlugins) {
            try {
                logger.info(`Processing plugin: ${plugin.slug}`);
                
                // Validate plugin metadata
                validatePluginMetadata(plugin);
                
                // Process the plugin
                await processPlugin(plugin);
                
                logger.info(`Successfully processed plugin: ${plugin.slug}`);
            } catch (error) {
                logger.error(`Error processing plugin ${plugin.slug}`, { error: error.message });
            }
        }
        
        logger.info('Plugin processing completed');
    } catch (error) {
        logger.error('Fatal error during plugin processing', { error: error.message });
        // Restore from backup if we have one
        if (backupPath) {
            try {
                await backupManager.restoreBackup(backupPath);
                logger.info('Successfully restored from backup');
            } catch (restoreError) {
                logger.error('Failed to restore from backup', { error: restoreError.message });
            }
        }
        process.exit(1);
    }
}

processPlugins(); 