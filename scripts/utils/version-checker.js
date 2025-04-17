const axios = require('axios');
const semver = require('semver');
const fs = require('fs').promises;
const path = require('path');
const Logger = require('./logger');

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
        
        // Configure axios instance with auth - token is already in base64 format
        this.api = axios.create({
            headers: {
                'Authorization': `Basic ${this.authToken}`,
                'Accept': 'application/json',
                'User-Agent': 'TechOps-Version-Checker'
            },
            timeout: 10000 // 10 second timeout
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

    async saveVersions(versions) {
        await fs.writeFile(this.versionsFile, JSON.stringify(versions, null, 2));
        this.logger.info('Versions file updated successfully');
    }

    async checkPluginUpdates(pluginSlug, currentVersion) {
        try {
            // First check if plugin exists in WordPress site
            const response = await this.api.get(`${this.siteUrl}/wp-json/techops/v1/plugins/list`);
            const plugin = response.data.find(p => p.slug === pluginSlug);
            
            if (!plugin) {
                this.logger.warn(`Plugin ${pluginSlug} not found in WordPress site`);
                return null;
            }

            // Compare versions
            return {
                currentVersion,
                latestVersion: plugin.version,
                requires: plugin.requires || 'N/A',
                lastUpdated: plugin.last_updated || 'N/A',
                hasUpdate: semver.gt(plugin.version, currentVersion)
            };
        } catch (error) {
            this.logger.error(`Error checking updates for plugin ${pluginSlug}: ${error.message}`);
            return null;
        }
    }

    async checkThemeUpdates(themeSlug, currentVersion) {
        try {
            // First check if theme exists in WordPress site
            const response = await this.api.get(`${this.siteUrl}/wp-json/techops/v1/themes/list`);
            const theme = response.data.find(t => t.slug === themeSlug);
            
            if (!theme) {
                this.logger.warn(`Theme ${themeSlug} not found in WordPress site`);
                return null;
            }

            // Compare versions
            return {
                currentVersion,
                latestVersion: theme.version,
                requires: theme.requires || 'N/A',
                lastUpdated: theme.last_updated || 'N/A',
                hasUpdate: semver.gt(theme.version, currentVersion)
            };
        } catch (error) {
            this.logger.error(`Error checking updates for theme ${themeSlug}: ${error.message}`);
            return null;
        }
    }

    async checkAllUpdates() {
        try {
            // Always fetch latest versions from WordPress API endpoints
            this.logger.info('Fetching latest versions from WordPress API endpoints...');
            const latestVersions = await this.fetchInstalledVersions();
            
            // Save the latest versions to versions.json
            await this.saveVersions(latestVersions);
            this.logger.info('Updated versions.json with latest data from API endpoints');
            
            const updates = {
                plugins: {},
                themes: {},
                timestamp: new Date().toISOString()
            };

            // Check plugin updates using the latest data
            for (const [slug, version] of Object.entries(latestVersions.plugins)) {
                const updateInfo = {
                    currentVersion: version,
                    latestVersion: version,
                    hasUpdate: false // Since we're using latest versions, there won't be updates
                };
                updates.plugins[slug] = updateInfo;
            }

            // Check theme updates using the latest data
            for (const [slug, version] of Object.entries(latestVersions.themes)) {
                const updateInfo = {
                    currentVersion: version,
                    latestVersion: version,
                    hasUpdate: false // Since we're using latest versions, there won't be updates
                };
                updates.themes[slug] = updateInfo;
            }

            // Save updates report to updates.json
            await fs.writeFile(
                path.join(process.cwd(), 'updates.json'),
                JSON.stringify(updates, null, 2)
            );

            return updates;
        } catch (error) {
            this.logger.error(`Error checking all updates: ${error.message}`);
            throw error;
        }
    }
}

module.exports = VersionChecker; 