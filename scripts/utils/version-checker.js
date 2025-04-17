const axios = require('axios');
const semver = require('semver');
const fs = require('fs').promises;
const path = require('path');
const Logger = require('./logger');

class VersionChecker {
    constructor() {
        this.logger = new Logger('VersionChecker');
        this.versionsFile = path.join(process.cwd(), 'config', 'versions.json');
        this.wordpressApi = 'https://api.wordpress.org';
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

    async saveVersions(versions) {
        await fs.mkdir(path.dirname(this.versionsFile), { recursive: true });
        await fs.writeFile(this.versionsFile, JSON.stringify(versions, null, 2));
        this.logger.info('Versions file updated successfully');
    }

    async checkPluginUpdates(pluginSlug, currentVersion) {
        try {
            const response = await axios.get(`${this.wordpressApi}/plugins/info/1.0/${pluginSlug}.json`);
            const latestVersion = response.data.version;
            const requires = response.data.requires;
            const lastUpdated = response.data.last_updated;

            return {
                currentVersion,
                latestVersion,
                requires,
                lastUpdated,
                hasUpdate: semver.gt(latestVersion, currentVersion)
            };
        } catch (error) {
            console.error(`Error checking updates for plugin ${pluginSlug}:`, error.message);
            return null;
        }
    }

    async checkThemeUpdates(themeSlug, currentVersion) {
        try {
            const response = await axios.get(`${this.wordpressApi}/themes/info/1.1/?action=theme_information&request[slug]=${themeSlug}`);
            const latestVersion = response.data.version;
            const requires = response.data.requires;
            const lastUpdated = response.data.last_updated;

            return {
                currentVersion,
                latestVersion,
                requires,
                lastUpdated,
                hasUpdate: semver.gt(latestVersion, currentVersion)
            };
        } catch (error) {
            console.error(`Error checking updates for theme ${themeSlug}:`, error.message);
            return null;
        }
    }

    async checkAllUpdates() {
        const versions = await this.loadVersions();
        const updates = {
            plugins: {},
            themes: {},
            timestamp: new Date().toISOString()
        };

        // Check plugin updates
        for (const [slug, version] of Object.entries(versions.plugins)) {
            const updateInfo = await this.checkPluginUpdates(slug, version);
            if (updateInfo) {
                updates.plugins[slug] = updateInfo;
            }
        }

        // Check theme updates
        for (const [slug, version] of Object.entries(versions.themes)) {
            const updateInfo = await this.checkThemeUpdates(slug, version);
            if (updateInfo) {
                updates.themes[slug] = updateInfo;
            }
        }

        // Save updates to file
        await fs.writeFile(
            path.join(process.cwd(), 'updates.json'),
            JSON.stringify(updates, null, 2)
        );

        return updates;
    }
}

module.exports = VersionChecker; 