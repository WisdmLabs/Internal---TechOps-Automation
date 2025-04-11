const fetch = require('node-fetch');

class DependencyChecker {
    constructor(siteUrl, authToken) {
        this.siteUrl = siteUrl;
        this.authToken = authToken;
    }

    async checkDependencies(plugin) {
        try {
            // Get plugin info from WordPress.org API
            const response = await fetch(
                `https://api.wordpress.org/plugins/info/1.0/${plugin.slug}.json`
            );
            const pluginInfo = await response.json();
            
            const results = {
                areMet: true,
                missing: [],
                details: {}
            };

            // Check PHP version compatibility
            if (pluginInfo.requires_php) {
                const phpVersion = await this.getPhpVersion();
                if (!this.checkVersion(phpVersion, pluginInfo.requires_php)) {
                    results.areMet = false;
                    results.missing.push(`PHP ${pluginInfo.requires_php}`);
                    results.details.php = {
                        required: pluginInfo.requires_php,
                        current: phpVersion
                    };
                }
            }
            
            // Check WordPress version compatibility
            if (pluginInfo.requires) {
                const wpVersion = await this.getWpVersion();
                if (!this.checkVersion(wpVersion, pluginInfo.requires)) {
                    results.areMet = false;
                    results.missing.push(`WordPress ${pluginInfo.requires}`);
                    results.details.wordpress = {
                        required: pluginInfo.requires,
                        current: wpVersion
                    };
                }
            }
            
            // Check plugin dependencies
            const dependencies = this.parseDependencies(pluginInfo);
            for (const dep of dependencies) {
                if (!await this.isPluginInstalled(dep)) {
                    results.areMet = false;
                    results.missing.push(`Plugin: ${dep}`);
                    results.details.plugins = results.details.plugins || [];
                    results.details.plugins.push(dep);
                }
            }
            
            return results;
        } catch (error) {
            throw new Error(`Dependency check failed: ${error.message}`);
        }
    }

    async getPhpVersion() {
        // Implement PHP version check
        return process.env.PHP_VERSION || '7.4';
    }

    async getWpVersion() {
        // Implement WordPress version check
        return process.env.WP_VERSION || '5.0';
    }

    checkVersion(current, required) {
        // Simple version comparison
        const currentParts = current.split('.').map(Number);
        const requiredParts = required.split('.').map(Number);
        
        for (let i = 0; i < Math.max(currentParts.length, requiredParts.length); i++) {
            const currentPart = currentParts[i] || 0;
            const requiredPart = requiredParts[i] || 0;
            
            if (currentPart > requiredPart) return true;
            if (currentPart < requiredPart) return false;
        }
        
        return true;
    }

    parseDependencies(pluginInfo) {
        // Parse plugin dependencies from plugin info
        return pluginInfo.dependencies || [];
    }

    async isPluginInstalled(pluginSlug) {
        try {
            const response = await fetch(
                `${this.siteUrl}/wp-json/techops/v1/plugins/list`,
                {
                    headers: {
                        'Authorization': `Basic ${this.authToken}`
                    }
                }
            );
            const plugins = await response.json();
            return plugins.some(p => p.slug === pluginSlug);
        } catch (error) {
            console.error(`Error checking plugin installation: ${error.message}`);
            return false;
        }
    }
}

module.exports = DependencyChecker; 