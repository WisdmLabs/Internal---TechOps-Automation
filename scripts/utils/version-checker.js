const axios = require('axios');
const path = require('path');
const fs = require('fs').promises;
const Logger = require('./logger');
const { normalizeVersion, compareVersions, validateApiResponse } = require('./version-utils');

class VersionChecker {
    constructor() {
        this.logger = new Logger('VersionChecker');
        this.configDir = path.join(process.cwd(), 'config');
        this.versionsFile = path.join(this.configDir, 'versions.json');
        
        // Get environment variables and secrets
        this.siteUrl = process.env.STAGING_SITE_URL;
        this.authToken = process.env.STAGING_SITE_AUTH_TOKEN;
        
        // Validate environment variables and secrets
        if (!this.siteUrl) {
            this.logger.error('Missing required repository variable: STAGING_SITE_URL');
            throw new Error('Missing required repository variable: STAGING_SITE_URL');
        }
        
        if (!this.authToken) {
            this.logger.error('Missing required repository secret: STAGING_SITE_AUTH_TOKEN');
            throw new Error('Missing required repository secret: STAGING_SITE_AUTH_TOKEN');
        }

        // Remove any trailing slashes from the URL
        this.siteUrl = this.siteUrl.replace(/\/+$/, '');
        
        // Configure custom API client
        this.customApi = axios.create({
            headers: {
                'Authorization': `Basic ${this.authToken}`,
                'Accept': 'application/json',
                'User-Agent': 'TechOps-Version-Checker'
            },
            timeout: 10000
        });

        // Configure WordPress.org API client
        this.wpApi = axios.create({
            headers: {
                'User-Agent': 'TechOps-Version-Checker/1.0'
            },
            timeout: 10000
        });
    }

    async fetchInstalledVersions() {
        try {
            // Fetch both plugins and themes from custom API
            const [pluginsResponse, themesResponse] = await Promise.all([
                this.customApi.get(`${this.siteUrl}/wp-json/techops/v1/plugins/list`),
                this.customApi.get(`${this.siteUrl}/wp-json/techops/v1/themes/list`)
            ]);

            // Validate API responses
            if (!validateApiResponse(pluginsResponse.data) || !validateApiResponse(themesResponse.data)) {
                throw new Error('Invalid API response format');
            }

            const versions = {
                plugins: {},
                themes: {}
            };

            // Process plugins with version normalization
            pluginsResponse.data.forEach(plugin => {
                if (plugin.slug && plugin.version) {
                    versions.plugins[plugin.slug] = {
                        currentVersion: plugin.version,
                        normalizedVersion: normalizeVersion(plugin.version)
                    };
                }
            });

            // Process themes with version normalization
            themesResponse.data.forEach(theme => {
                if (theme.slug && theme.version) {
                    versions.themes[theme.slug] = {
                        currentVersion: theme.version,
                        normalizedVersion: normalizeVersion(theme.version)
                    };
                }
            });

            return versions;
        } catch (error) {
            this.logger.error(`Error fetching installed versions: ${error.message}`);
            throw error;
        }
    }

    async fetchWordPressPluginInfo(slug) {
        try {
            const response = await this.wpApi.get(`https://api.wordpress.org/plugins/info/1.0/${slug}.json`);
            if (!response.data || !response.data.version) {
                throw new Error(`No version information found for plugin: ${slug}`);
            }
            return {
                version: response.data.version,
                requires: response.data.requires || 'N/A',
                last_updated: response.data.last_updated || 'N/A'
            };
        } catch (error) {
            this.logger.error(`Error fetching WordPress.org plugin info for ${slug}: ${error.message}`);
            return null;
        }
    }

    async fetchWordPressThemeInfo(slug) {
        try {
            const response = await this.wpApi.get(`https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=${slug}`);
            if (!response.data || !response.data.version) {
                throw new Error(`No version information found for theme: ${slug}`);
            }
            return {
                version: response.data.version,
                requires: response.data.requires || 'N/A',
                last_updated: response.data.last_updated || 'N/A'
            };
        } catch (error) {
            this.logger.error(`Error fetching WordPress.org theme info for ${slug}: ${error.message}`);
            return null;
        }
    }

    async checkPluginUpdates(slug, versionInfo) {
        try {
            const wpInfo = await this.fetchWordPressPluginInfo(slug);
            if (!wpInfo) {
                this.logger.warn(`Could not fetch WordPress.org info for plugin ${slug}`);
                return null;
            }

            const comparison = compareVersions(versionInfo.currentVersion, wpInfo.version);
            return {
                currentVersion: versionInfo.currentVersion,
                latestVersion: wpInfo.version,
                requires: wpInfo.requires,
                lastUpdated: wpInfo.last_updated,
                hasUpdate: comparison.hasUpdate
            };
        } catch (error) {
            this.logger.error(`Error checking updates for plugin ${slug}: ${error.message}`);
            return null;
        }
    }

    async checkThemeUpdates(slug, versionInfo) {
        try {
            const wpInfo = await this.fetchWordPressThemeInfo(slug);
            if (!wpInfo) {
                this.logger.warn(`Could not fetch WordPress.org info for theme ${slug}`);
                return null;
            }

            const comparison = compareVersions(versionInfo.currentVersion, wpInfo.version);
            return {
                currentVersion: versionInfo.currentVersion,
                latestVersion: wpInfo.version,
                requires: wpInfo.requires,
                lastUpdated: wpInfo.last_updated,
                hasUpdate: comparison.hasUpdate
            };
        } catch (error) {
            this.logger.error(`Error checking updates for theme ${slug}: ${error.message}`);
            return null;
        }
    }

    async checkAllUpdates() {
        try {
            // Get current versions from WordPress site
            this.logger.info('Fetching current versions from WordPress site...');
            const versions = await this.fetchInstalledVersions();
            
            // Save current versions to file
            await fs.writeFile(this.versionsFile, JSON.stringify(versions, null, 2));
            this.logger.info('Saved current versions to versions.json');
            
            const updates = {
                plugins: {},
                themes: {},
                timestamp: new Date().toISOString()
            };

            // Check plugin updates
            this.logger.info('Checking plugin updates...');
            for (const [slug, versionInfo] of Object.entries(versions.plugins)) {
                const updateInfo = await this.checkPluginUpdates(slug, versionInfo);
                if (updateInfo) {
                    updates.plugins[slug] = updateInfo;
                }
            }

            // Check theme updates
            this.logger.info('Checking theme updates...');
            for (const [slug, versionInfo] of Object.entries(versions.themes)) {
                const updateInfo = await this.checkThemeUpdates(slug, versionInfo);
                if (updateInfo) {
                    updates.themes[slug] = updateInfo;
                }
            }

            // Save updates report
            await fs.writeFile(
                path.join(process.cwd(), 'updates.json'),
                JSON.stringify(updates, null, 2)
            );
            this.logger.info('Saved updates report to updates.json');

            return updates;
        } catch (error) {
            this.logger.error(`Error checking all updates: ${error.message}`);
            throw error;
        }
    }
}

module.exports = VersionChecker; 