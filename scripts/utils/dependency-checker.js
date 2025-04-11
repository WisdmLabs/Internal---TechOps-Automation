const Logger = require('./logger');

class DependencyChecker {
    constructor() {
        this.logger = new Logger('DependencyChecker');
    }

    async getWordPressVersion(siteUrl, authToken) {
        try {
            const response = await fetch(`${siteUrl}/wp-json/`, {
                headers: {
                    'Authorization': `Basic ${authToken}`
                }
            });
            const data = await response.json();
            return data.version;
        } catch (error) {
            this.logger.error('Failed to get WordPress version', { error: error.message });
            throw error;
        }
    }

    async checkDependencies(plugin, siteUrl, authToken) {
        try {
            const currentVersion = await this.getWordPressVersion(siteUrl, authToken);
            const dependencies = {
                areMet: true,
                missing: [],
                details: {}
            };

            if (plugin.requires_wp) {
                const requiredVersion = plugin.requires_wp;
                if (this.compareVersions(currentVersion, requiredVersion) < 0) {
                    dependencies.areMet = false;
                    dependencies.missing.push(`WordPress ${requiredVersion}`);
                    dependencies.details.wordpress = {
                        required: requiredVersion,
                        current: currentVersion
                    };
                }
            }

            return dependencies;
        } catch (error) {
            this.logger.error('Dependency check failed', { error: error.message });
            throw error;
        }
    }

    compareVersions(v1, v2) {
        const v1Parts = v1.split('.').map(Number);
        const v2Parts = v2.split('.').map(Number);
        
        for (let i = 0; i < Math.max(v1Parts.length, v2Parts.length); i++) {
            const v1Part = v1Parts[i] || 0;
            const v2Part = v2Parts[i] || 0;
            if (v1Part > v2Part) return 1;
            if (v1Part < v2Part) return -1;
        }
        return 0;
    }
}

module.exports = DependencyChecker; 