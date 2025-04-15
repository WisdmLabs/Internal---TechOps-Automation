#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const BASE_DIR = 'wp-content/themes';
const THEME_SLUG = process.env.THEME_SLUG || '';

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

        console.log(`Processing ${themesToProcess.length} themes...`);

        for (const theme of themesToProcess) {
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
                    // If there's only one directory, move its contents
                    fs.renameSync(path.join(tempDir, extractedFiles[0]), themeDir);
                } else {
                    // Otherwise move all files directly
                    fs.renameSync(tempDir, themeDir);
                }

                // Clean up
                fs.unlinkSync(zipPath);
                if (fs.existsSync(tempDir)) {
                    fs.rmSync(tempDir, { recursive: true, force: true });
                }

                console.log(`✅ Successfully processed ${theme.slug}`);
            } catch (error) {
                console.error(`❌ Error processing theme ${theme.slug}:`, error.message);
                // The shell script will handle restoration from backup
            }
        }

        console.log('\n✅ Theme processing completed successfully');
    } catch (error) {
        console.error('❌ Error processing themes:', error.message);
        process.exit(1);
    }
}

// Read themes list from stdin
const themesList = JSON.parse(fs.readFileSync(0, 'utf-8'));
processThemes(themesList).catch(error => {
    console.error('❌ Fatal error:', error.message);
    process.exit(1);
}); 