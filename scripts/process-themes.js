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
                const tempDir = path.join(BASE_DIR, '_temp_extract');

                console.log(`\nProcessing theme: ${theme.slug}`);

                // Download theme ZIP
                console.log(`Downloading ${theme.slug}...`);
                execSync(
                    `curl -H "Authorization: Basic ${process.env.WP_AUTH_TOKEN}" ` +
                    `"${process.env.SITE_URL}/wp-json/techops/v1/themes/download/${theme.slug}" ` +
                    `--output "${zipPath}" --fail --silent --show-error`
                );

                // Create temp directory for extraction
                if (!fs.existsSync(tempDir)) {
                    fs.mkdirSync(tempDir, { recursive: true });
                }

                // Extract ZIP to temp directory with force overwrite
                console.log(`Extracting ${theme.slug}...`);
                execSync(`unzip -o -q "${zipPath}" -d "${tempDir}"`);

                // Remove existing theme directory if it exists
                if (fs.existsSync(themeDir)) {
                    console.log(`Removing existing ${theme.slug} directory...`);
                    fs.rmSync(themeDir, { recursive: true, force: true });
                }

                // Move from temp directory to final location
                const extractedFiles = fs.readdirSync(tempDir);
                if (extractedFiles.length === 1 && fs.statSync(path.join(tempDir, extractedFiles[0])).isDirectory()) {
                    // If extracted to a single directory, move that directory
                    fs.renameSync(path.join(tempDir, extractedFiles[0]), themeDir);
                } else {
                    // If extracted multiple files, move them all to a new directory
                    fs.mkdirSync(themeDir, { recursive: true });
                    extractedFiles.forEach(file => {
                        fs.renameSync(path.join(tempDir, file), path.join(themeDir, file));
                    });
                }

                // Clean up
                console.log(`Cleaning up ${theme.slug}...`);
                fs.rmSync(zipPath);
                fs.rmSync(tempDir, { recursive: true, force: true });

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