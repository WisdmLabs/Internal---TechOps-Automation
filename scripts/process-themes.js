#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const BASE_DIR = 'wp-content/themes';

async function processThemes(themesList) {
    try {
        // Ensure the themes directory exists
        if (!fs.existsSync(BASE_DIR)) {
            fs.mkdirSync(BASE_DIR, { recursive: true });
        }

        console.log(`Processing ${themesList.length} themes...`);

        for (const theme of themesList) {
            try {
                const themeDir = path.join(BASE_DIR, theme.slug);
                const zipPath = path.join(BASE_DIR, `${theme.slug}.zip`);

                console.log(`\nProcessing theme: ${theme.slug}`);

                // Download theme ZIP
                console.log(`Downloading ${theme.slug}...`);
                execSync(
                    `curl -H "Authorization: Basic ${process.env.WP_AUTH_TOKEN}" ` +
                    `"${process.env.SITE_URL}/wp-json/techops/v1/themes/download/${theme.slug}" ` +
                    `--output "${zipPath}" --fail --silent --show-error`
                );

                // Remove existing theme directory if it exists
                if (fs.existsSync(themeDir)) {
                    console.log(`Removing existing ${theme.slug} directory...`);
                    fs.rmSync(themeDir, { recursive: true, force: true });
                }

                // Extract ZIP
                console.log(`Extracting ${theme.slug}...`);
                execSync(`unzip -q "${zipPath}" -d "${BASE_DIR}"`);

                // Clean up ZIP file
                console.log(`Cleaning up ${theme.slug} zip file...`);
                fs.unlinkSync(zipPath);

                console.log(`Successfully processed ${theme.slug}`);
            } catch (error) {
                console.error(`Error processing theme ${theme.slug}:`, error.message);
            }
        }
    } catch (error) {
        console.error('Error in theme processing:', error.message);
        process.exit(1);
    }
}

// Read themes list from stdin
let data = '';
process.stdin.on('data', chunk => {
    data += chunk;
});

process.stdin.on('end', () => {
    try {
        const themesList = JSON.parse(data);
        processThemes(themesList).catch(error => {
            console.error('Error:', error.message);
            process.exit(1);
        });
    } catch (error) {
        console.error('Error parsing themes list:', error.message);
        process.exit(1);
    }
}); 