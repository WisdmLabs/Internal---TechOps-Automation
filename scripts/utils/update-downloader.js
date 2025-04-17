const axios = require('axios');
const fs = require('fs').promises;
const path = require('path');
const { execSync } = require('child_process');
const Logger = require('./logger');

class UpdateDownloader {
    constructor() {
        this.logger = new Logger('UpdateDownloader');
        this.tempDir = path.join(process.cwd(), 'temp-updates');
        this.updatesFile = path.join(process.cwd(), 'updates.json');
    }

    async loadUpdates() {
        try {
            const data = await fs.readFile(this.updatesFile, 'utf8');
            return JSON.parse(data);
        } catch (error) {
            this.logger.error(`Error loading updates file: ${error.message}`);
            return { plugins: {}, themes: {} };
        }
    }

    async downloadPlugin(slug, downloadUrl) {
        try {
            this.logger.info(`Downloading plugin: ${slug}`);
            
            // Create temp directory if it doesn't exist
            await fs.mkdir(this.tempDir, { recursive: true });
            
            const zipPath = path.join(this.tempDir, `${slug}.zip`);
            
            // Download the plugin
            const response = await axios({
                method: 'get',
                url: downloadUrl,
                responseType: 'arraybuffer'
            });
            
            // Save the file
            await fs.writeFile(zipPath, response.data);
            
            // Verify the file
            if (!await this.verifyZipFile(zipPath)) {
                throw new Error(`Invalid ZIP file for plugin ${slug}`);
            }
            
            this.logger.info(`Successfully downloaded plugin: ${slug}`);
            return zipPath;
        } catch (error) {
            this.logger.error(`Error downloading plugin ${slug}: ${error.message}`);
            return null;
        }
    }

    async downloadTheme(slug, downloadUrl) {
        try {
            this.logger.info(`Downloading theme: ${slug}`);
            
            // Create temp directory if it doesn't exist
            await fs.mkdir(this.tempDir, { recursive: true });
            
            const zipPath = path.join(this.tempDir, `${slug}.zip`);
            
            // Download the theme
            const response = await axios({
                method: 'get',
                url: downloadUrl,
                responseType: 'arraybuffer'
            });
            
            // Save the file
            await fs.writeFile(zipPath, response.data);
            
            // Verify the file
            if (!await this.verifyZipFile(zipPath)) {
                throw new Error(`Invalid ZIP file for theme ${slug}`);
            }
            
            this.logger.info(`Successfully downloaded theme: ${slug}`);
            return zipPath;
        } catch (error) {
            this.logger.error(`Error downloading theme ${slug}: ${error.message}`);
            return null;
        }
    }

    async verifyZipFile(filePath) {
        try {
            execSync(`unzip -t "${filePath}" > /dev/null`);
            return true;
        } catch (error) {
            return false;
        }
    }

    async cleanup() {
        try {
            await fs.rm(this.tempDir, { recursive: true, force: true });
            this.logger.info('Temporary files cleaned up');
        } catch (error) {
            this.logger.error(`Error cleaning up temporary files: ${error.message}`);
        }
    }

    async downloadUpdates(type) {
        try {
            const updates = await this.loadUpdates();
            const wordpressApi = 'https://api.wordpress.org';
            
            if (type === 'plugins' || type === 'all') {
                this.logger.info('Downloading plugin updates...');
                for (const [slug, info] of Object.entries(updates.plugins)) {
                    if (info.hasUpdate) {
                        const downloadUrl = `${wordpressApi}/plugins/download/${slug}.zip`;
                        await this.downloadPlugin(slug, downloadUrl);
                    }
                }
            }
            
            if (type === 'themes' || type === 'all') {
                this.logger.info('Downloading theme updates...');
                for (const [slug, info] of Object.entries(updates.themes)) {
                    if (info.hasUpdate) {
                        const downloadUrl = `${wordpressApi}/themes/download/${slug}.zip`;
                        await this.downloadTheme(slug, downloadUrl);
                    }
                }
            }
            
            this.logger.info('Update downloads completed successfully');
        } catch (error) {
            this.logger.error(`Error downloading updates: ${error.message}`);
            throw error;
        }
    }
}

// If running directly (not imported as a module)
if (require.main === module) {
    const downloader = new UpdateDownloader();
    const type = process.argv[2] || 'all';
    
    if (type !== 'all' && type !== 'plugins' && type !== 'themes') {
        console.error('Invalid type. Must be one of: all, plugins, themes');
        process.exit(1);
    }
    
    downloader.downloadUpdates(type)
        .then(() => {
            console.log(`Successfully downloaded ${type} updates`);
            process.exit(0);
        })
        .catch(error => {
            console.error('Error downloading updates:', error.message);
            process.exit(1);
        });
}

module.exports = UpdateDownloader; 