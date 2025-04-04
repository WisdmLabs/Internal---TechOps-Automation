#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const BASE_DIR = 'wp-content/plugins';

async function processPlugins(pluginsList) {
    try {
        // Ensure the plugins directory exists
        if (!fs.existsSync(BASE_DIR)) {
            fs.mkdirSync(BASE_DIR, { recursive: true });
        }

        console.log(`Processing ${pluginsList.length} plugins...`);

        for (const plugin of pluginsList) {
            try {
                const pluginDir = path.join(BASE_DIR, plugin.slug);
                const zipPath = path.join(BASE_DIR, `${plugin.slug}.zip`);

                console.log(`\nProcessing plugin: ${plugin.slug}`);

                // Download plugin ZIP
                console.log(`Downloading ${plugin.slug}...`);
                execSync(
                    `curl -H "Authorization: Basic ${process.env.WP_AUTH_TOKEN}" ` +
                    `"${process.env.SITE_URL}/wp-json/techops/v1/plugins/download/${plugin.slug}" ` +
                    `--output "${zipPath}" --fail --silent --show-error`
                );

                // Remove existing plugin directory if it exists
                if (fs.existsSync(pluginDir)) {
                    console.log(`Removing existing ${plugin.slug} directory...`);
                    fs.rmSync(pluginDir, { recursive: true, force: true });
                }

                // Extract ZIP
                console.log(`Extracting ${plugin.slug}...`);
                execSync(`unzip -q "${zipPath}" -d "${BASE_DIR}"`);

                // Clean up ZIP file
                console.log(`Cleaning up ${plugin.slug} zip file...`);
                fs.unlinkSync(zipPath);

                console.log(`Successfully processed ${plugin.slug}`);
            } catch (error) {
                console.error(`Error processing plugin ${plugin.slug}:`, error.message);
            }
        }
    } catch (error) {
        console.error('Error in plugin processing:', error.message);
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
            console.error('Error:', error.message);
            process.exit(1);
        });
    } catch (error) {
        console.error('Error parsing plugins list:', error.message);
        process.exit(1);
    }
}); 