#!/usr/bin/env node

const fs = require('fs').promises;
const path = require('path');
const Logger = require('./logger');
const axios = require('axios');

class VersionUpdater {
    constructor() {
        this.logger = new Logger('VersionUpdater');
        this.configDir = path.join(process.cwd(), 'config');
        this.versionsFile = path.join(this.configDir, 'versions.json');
        this.updatesFile = path.join(process.cwd(), 'updates.json');

        // Get environment variables
        this.siteUrl = process.env.LIVE_SITE_URL || process.env.STAGING_SITE_URL;
        this.authToken = process.env.LIVE_SITE_AUTH_TOKEN || process.env.STAGING_SITE_AUTH_TOKEN;
        
        if (!this.siteUrl || !this.authToken) {
            this.logger.error('Missing required environment variables: LIVE_SITE_URL/STAGING_SITE_URL and LIVE_SITE_AUTH_TOKEN/STAGING_SITE_AUTH_TOKEN');
        }

        // Configure axios instance with auth
        this.api = axios.create({
            headers: {
                'Authorization': `Basic ${this.authToken}`,
                'Accept': 'application/json'
            }
        });
    }

    async fetchInstalledVersions() {
        try {
            // Fetch both plugins and themes in parallel
            const [pluginsResponse, themesResponse] = await Promise.all([
                this.api.get(`${this.siteUrl}/wp-json/techops/v1/plugins/list`),
                this.api.get(`${this.siteUrl}/wp-json/techops/v1/themes/list`)
            ]);

            const versions = {
                plugins: {},
                themes: {}
            };

            // Process plugins
            if (pluginsResponse.data && Array.isArray(pluginsResponse.data)) {
                pluginsResponse.data.forEach(plugin => {
                    if (plugin.slug && plugin.version) {
                        versions.plugins[plugin.slug] = plugin.version;
                    }
                });
            }

            // Process themes
            if (themesResponse.data && Array.isArray(themesResponse.data)) {
                themesResponse.data.forEach(theme => {
                    if (theme.slug && theme.version) {
                        versions.themes[theme.slug] = theme.version;
                    }
                });
            }

            return versions;
        } catch (error) {
            this.logger.error(`Error fetching installed versions: ${error.message}`);
            throw error;
        }
    }

    async loadVersions() {
        try {
            // Create config directory if it doesn't exist
            try {
                await fs.access(this.configDir);
            } catch (error) {
                this.logger.info('Config directory not found. Creating it...');
                await fs.mkdir(this.configDir, { recursive: true });
            }

            // Try to read the versions file or fetch from WordPress
            try {
                const data = await fs.readFile(this.versionsFile, 'utf8');
                return JSON.parse(data);
            } catch (error) {
                if (error.code === 'ENOENT') {
                    this.logger.info('No versions file found. Fetching from WordPress...');
                    const versions = await this.fetchInstalledVersions();
                    await this.saveVersions(versions);
                    return versions;
                }
                throw error;
            }
        } catch (error) {
            this.logger.error(`Error handling versions file: ${error.message}`);
            return { plugins: {}, themes: {} };
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
        await fs.writeFile(this.versionsFile, JSON.stringify(versions, null, 2));
        this.logger.info('Versions file updated successfully');
    }

    async updateVersions() {
        try {
            const versions = await this.loadVersions();
            const updates = await this.loadUpdates();
            const latestVersions = await this.fetchInstalledVersions();
            
            let hasChanges = false;
            
            // Update plugin versions
            for (const [slug, info] of Object.entries(updates.plugins)) {
                if (info.hasUpdate && latestVersions.plugins[slug]) {
                    versions.plugins[slug] = latestVersions.plugins[slug];
                    this.logger.info(`Updated plugin ${slug} version to ${latestVersions.plugins[slug]}`);
                    hasChanges = true;
                }
            }
            
            // Update theme versions
            for (const [slug, info] of Object.entries(updates.themes)) {
                if (info.hasUpdate && latestVersions.themes[slug]) {
                    versions.themes[slug] = latestVersions.themes[slug];
                    this.logger.info(`Updated theme ${slug} version to ${latestVersions.themes[slug]}`);
                    hasChanges = true;
                }
            }
            
            if (hasChanges) {
                await this.saveVersions(versions);
                this.logger.info('Version update completed successfully');
            } else {
                this.logger.info('No version updates were necessary');
            }
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