const fetch = require('node-fetch');

class DependencyChecker {
    constructor(siteUrl, authToken) {
        this.siteUrl = siteUrl;
        this.authToken = authToken;
        this.rateLimitDelay = 1000; // 1 second delay between requests
        this.lastRequestTime = 0;
    }

    async makeRequest(url) {
        // Implement rate limiting
        const now = Date.now();
        const timeSinceLastRequest = now - this.lastRequestTime;
        if (timeSinceLastRequest < this.rateLimitDelay) {
            await new Promise(resolve => setTimeout(resolve, this.rateLimitDelay - timeSinceLastRequest));
        }
        
        const response = await fetch(url);
        this.lastRequestTime = Date.now();
        
        if (response.status === 429) {
            // Rate limited, wait and retry
            const retryAfter = response.headers.get('Retry-After') || 5;
            await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
            return this.makeRequest(url);
        }
        
        return response;
    }

    async checkDependencies(plugin) {
        try {
            // Validate plugin slug
            if (!plugin.slug || !/^[a-z0-9-]+$/.test(plugin.slug)) {
                throw new Error('Invalid plugin slug');
            }
            
            // Get plugin info from WordPress.org API
            const response = await this.makeRequest(
                `https://api.wordpress.org/plugins/info/1.0/${plugin.slug}.json`
            );
            
            if (!response.ok) {
                throw new Error(`WordPress.org API returned status ${response.status}`);
            }
            
            const pluginInfo = await response.json();
            if (!pluginInfo || typeof pluginInfo !== 'object') {
                throw new Error('Invalid response from WordPress.org API');
            }
            
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
        try {
            const response = await this.makeRequest(
                `${this.siteUrl}/wp-json/techops/v1/system/php-version`
            );
            
            if (!response.ok) {
                return process.env.PHP_VERSION || '7.4';
            }
            
            const data = await response.json();
            return data.version || process.env.PHP_VERSION || '7.4';
        } catch (error) {
            return process.env.PHP_VERSION || '7.4';
        }
    }

    async getWpVersion() {
        try {
            const response = await this.makeRequest(
                `${this.siteUrl}/wp-json/techops/v1/system/wordpress-version`
            );
            
            if (!response.ok) {
                return process.env.WP_VERSION || '5.0';
            }
            
            const data = await response.json();
            return data.version || process.env.WP_VERSION || '5.0';
        } catch (error) {
            return process.env.WP_VERSION || '5.0';
        }
    }

    checkVersion(current, required) {
        if (!current || !required) {
            return true;
        }
        
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
        if (!pluginInfo || !pluginInfo.dependencies) {
            return [];
        }
        
        // Ensure dependencies is an array
        return Array.isArray(pluginInfo.dependencies) 
            ? pluginInfo.dependencies 
            : [];
    }

    async isPluginInstalled(pluginSlug) {
        try {
            if (!pluginSlug || !/^[a-z0-9-]+$/.test(pluginSlug)) {
                return false;
            }
            
            const response = await this.makeRequest(
                `${this.siteUrl}/wp-json/techops/v1/plugins/list`
            );
            
            if (!response.ok) {
                throw new Error(`API returned status ${response.status}`);
            }
            
            const plugins = await response.json();
            if (!Array.isArray(plugins)) {
                throw new Error('Invalid response format');
            }
            
            return plugins.some(p => p.slug === pluginSlug);
        } catch (error) {
            console.error(`Error checking plugin ${pluginSlug}:`, error.message);
            return false;
        }
    }
}

module.exports = DependencyChecker; 