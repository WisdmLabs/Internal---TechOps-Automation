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

        for (const plugin of pluginsList) {
            try {
                const pluginDir = path.join(BASE_DIR, plugin.slug);
                const zipPath = path.join(BASE_DIR, `${plugin.slug}.zip`);
                const tempDir = path.join(BASE_DIR, '_temp_extract');

                console.log(`\nProcessing plugin: ${plugin.slug}`);

                // Download plugin ZIP
                console.log(`Downloading ${plugin.slug}...`);
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
                console.log(`Extracting ${plugin.slug}...`);
                execSync(`unzip -o -q "${zipPath}" -d "${tempDir}"`);

                // Remove existing plugin directory if it exists
                if (fs.existsSync(pluginDir)) {
                    console.log(`Removing existing ${plugin.slug} directory...`);
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
                console.log(`Cleaning up ${plugin.slug}...`);
                fs.rmSync(zipPath);
                fs.rmSync(tempDir, { recursive: true, force: true });

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