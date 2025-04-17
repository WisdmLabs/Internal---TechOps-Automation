#!/usr/bin/env node

const fs = require('fs').promises;
const path = require('path');
const axios = require('axios');
const AdmZip = require('adm-zip');
const Logger = require('./utils/logger');

class UpdateProcessor {
    constructor() {
        this.logger = new Logger('UpdateProcessor');
        this.updateReport = null;
        this.downloadDir = path.join(process.cwd(), 'downloads');
        this.pluginsDir = path.join(process.cwd(), 'wp-content', 'plugins');
        this.themesDir = path.join(process.cwd(), 'wp-content', 'themes');
    }

    async readUpdateReport() {
        try {
            const reportPath = path.join(process.cwd(), 'update_report.txt');
            const reportContent = await fs.readFile(reportPath, 'utf8');
            this.updateReport = JSON.parse(reportContent);
            this.logger.info('Successfully read update report');
        } catch (error) {
            this.logger.error(`Error reading update report: ${error.message}`);
            throw error;
        }
    }

    async createDirectories() {
        try {
            await fs.mkdir(this.downloadDir, { recursive: true });
            await fs.mkdir(this.pluginsDir, { recursive: true });
            await fs.mkdir(this.themesDir, { recursive: true });
            this.logger.info('Created necessary directories');
        } catch (error) {
            this.logger.error(`Error creating directories: ${error.message}`);
            throw error;
        }
    }

    async downloadPlugin(slug) {
        try {
            const url = `https://downloads.wordpress.org/plugin/${slug}.zip`;
            const response = await axios({
                method: 'get',
                url: url,
                responseType: 'arraybuffer'
            });

            const zipPath = path.join(this.downloadDir, `${slug}.zip`);
            await fs.writeFile(zipPath, response.data);
            this.logger.info(`Downloaded plugin: ${slug}`);
            return zipPath;
        } catch (error) {
            this.logger.error(`Error downloading plugin ${slug}: ${error.message}`);
            return null;
        }
    }

    async extractPlugin(zipPath, slug) {
        try {
            const zip = new AdmZip(zipPath);
            const targetDir = path.join(this.pluginsDir, slug);
            
            // Remove existing plugin directory if it exists
            try {
                await fs.rm(targetDir, { recursive: true, force: true });
            } catch (error) {
                // Ignore if directory doesn't exist
            }

            zip.extractAllTo(targetDir, true);
            this.logger.info(`Extracted plugin: ${slug}`);
            
            // Clean up zip file
            await fs.unlink(zipPath);
        } catch (error) {
            this.logger.error(`Error extracting plugin ${slug}: ${error.message}`);
        }
    }

    async processUpdates(updateType = 'all') {
        try {
            await this.readUpdateReport();
            await this.createDirectories();

            const updates = {
                processed: [],
                failed: [],
                skipped: []
            };

            if (updateType === 'all' || updateType === 'plugins') {
                for (const [slug, info] of Object.entries(this.updateReport.plugins)) {
                    if (info.hasUpdate) {
                        this.logger.info(`Processing plugin update: ${slug}`);
                        const zipPath = await this.downloadPlugin(slug);
                        
                        if (zipPath) {
                            await this.extractPlugin(zipPath, slug);
                            updates.processed.push({
                                type: 'plugin',
                                slug,
                                from: info.currentVersion,
                                to: info.latestVersion
                            });
                        } else {
                            updates.failed.push({
                                type: 'plugin',
                                slug,
                                reason: 'Download failed'
                            });
                        }
                    } else {
                        updates.skipped.push({
                            type: 'plugin',
                            slug,
                            reason: 'No update available'
                        });
                    }
                }
            }

            // Save update results
            const resultsPath = path.join(process.cwd(), 'update_results.json');
            await fs.writeFile(resultsPath, JSON.stringify(updates, null, 2));
            this.logger.info('Update processing completed');
            
            return updates;
        } catch (error) {
            this.logger.error(`Error processing updates: ${error.message}`);
            throw error;
        }
    }
}

// Main execution
async function main() {
    try {
        const processor = new UpdateProcessor();
        const updateType = process.env.UPDATE_TYPE || 'all';
        await processor.processUpdates(updateType);
    } catch (error) {
        console.error('Error running update processor:', error);
        process.exit(1);
    }
}

// Run the main function
if (require.main === module) {
    main();
}

module.exports = UpdateProcessor; 