#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const BackupManager = require('./backup-manager');
const DependencyChecker = require('./dependency-checker');
const Logger = require('./logger');

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
const BASE_DIR = path.join('wp-content', 'plugins');
const EXCLUDED_PLUGINS = ['techops-content-sync'];
const LOG_FILE = path.join(BASE_DIR, 'plugin-sync.log');

// Initialize utilities
const backupManager = new BackupManager(BASE_DIR);
const dependencyChecker = new DependencyChecker(process.env.LIVE_SITE_URL, process.env.LIVE_SITE_AUTH_TOKEN);
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
    
    if (!/^[a-z0-9-]+$/.test(plugin.slug)) {
        throw new Error('Invalid plugin slug format');
    }
    
    return true;
}

async function processPlugins() {
    try {
        logger.info('Starting plugin processing...');
        
        // Create backup
        const backupPath = await backupManager.createBackup();
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
        
        fs.writeFileSync(
            path.join(BASE_DIR, 'activation-states.json'),
            JSON.stringify(activationStates, null, 2)
        );
        
        // Process each plugin
        for (const plugin of filteredPlugins) {
            try {
                logger.info(`Processing plugin: ${plugin.slug}`);
                
                // Validate plugin metadata
                validatePluginMetadata(plugin);
                
                // Check dependencies
                const dependencies = await dependencyChecker.checkDependencies(plugin);
                if (!dependencies.areMet) {
                    logger.warn(`Plugin ${plugin.slug} has unmet dependencies`, { dependencies });
                }
                
                // Download plugin
                const pluginUrl = `${process.env.LIVE_SITE_URL}/wp-json/techops/v1/plugins/download/${plugin.slug}`;
                const zipPath = path.join(BASE_DIR, `${plugin.slug}.zip`);
                
                try {
                    execSync(`curl -s -H "Authorization: Basic ${process.env.LIVE_SITE_AUTH_TOKEN}" "${pluginUrl}" -o "${zipPath}"`);
                } catch (error) {
                    logger.error(`Failed to download plugin ${plugin.slug}`, { error: error.message });
                    continue;
                }
                
                // Extract plugin
                const extractPath = path.join(BASE_DIR, plugin.slug);
                try {
                    execSync(`unzip -q -o "${zipPath}" -d "${extractPath}"`);
                } catch (error) {
                    logger.error(`Failed to extract plugin ${plugin.slug}`, { error: error.message });
                    fs.unlinkSync(zipPath);
                    continue;
                }
                
                // Clean up zip file
                fs.unlinkSync(zipPath);
                
                logger.info(`Successfully processed plugin: ${plugin.slug}`);
            } catch (error) {
                logger.error(`Error processing plugin ${plugin.slug}`, { error: error.message });
            }
        }
        
        logger.info('Plugin processing completed');
    } catch (error) {
        logger.error('Fatal error during plugin processing', { error: error.message });
        // Restore from backup
        await backupManager.restoreBackup(backupPath);
        process.exit(1);
    }
}

processPlugins(); 