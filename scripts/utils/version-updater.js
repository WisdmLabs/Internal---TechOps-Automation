#!/usr/bin/env node

const fs = require('fs').promises;
const path = require('path');
const Logger = require('./logger');

class VersionUpdater {
    constructor() {
        this.logger = new Logger('VersionUpdater');
        this.versionsFile = path.join(process.cwd(), 'config', 'versions.json');
        this.updatesFile = path.join(process.cwd(), 'updates.json');
    }

    async loadVersions() {
        try {
            const data = await fs.readFile(this.versionsFile, 'utf8');
            return JSON.parse(data);
        } catch (error) {
            if (error.code === 'ENOENT') {
                this.logger.info('No versions file found, creating new one');
                return { plugins: {}, themes: {} };
            }
            throw error;
        }
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

    async saveVersions(versions) {
        await fs.mkdir(path.dirname(this.versionsFile), { recursive: true });
        await fs.writeFile(this.versionsFile, JSON.stringify(versions, null, 2));
        this.logger.info('Versions file updated successfully');
    }

    async updateVersions() {
        try {
            const versions = await this.loadVersions();
            const updates = await this.loadUpdates();
            
            // Update plugin versions
            for (const [slug, info] of Object.entries(updates.plugins)) {
                if (info.hasUpdate) {
                    versions.plugins[slug] = info.latestVersion;
                    this.logger.info(`Updated plugin ${slug} version to ${info.latestVersion}`);
                }
            }
            
            // Update theme versions
            for (const [slug, info] of Object.entries(updates.themes)) {
                if (info.hasUpdate) {
                    versions.themes[slug] = info.latestVersion;
                    this.logger.info(`Updated theme ${slug} version to ${info.latestVersion}`);
                }
            }
            
            await this.saveVersions(versions);
            this.logger.info('Version update completed successfully');
        } catch (error) {
            this.logger.error(`Error updating versions: ${error.message}`);
            throw error;
        }
    }
}

// If running directly (not imported as a module)
if (require.main === module) {
    const updater = new VersionUpdater();
    updater.updateVersions()
        .then(() => {
            console.log('Version update completed successfully');
            process.exit(0);
        })
        .catch(error => {
            console.error('Error updating versions:', error.message);
            process.exit(1);
        });
}

module.exports = VersionUpdater; 