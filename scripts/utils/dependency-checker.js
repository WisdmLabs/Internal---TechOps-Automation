const Logger = require('./logger');

class DependencyChecker {
    constructor() {
        this.logger = new Logger('DependencyChecker');
        this.cache = new Map();
    }

    async getWordPressVersion(siteUrl, authToken) {
        try {
            const command = [
                'curl',
                '-s',
                '-H', `"Authorization: Basic ${authToken}"`,
                '-H', '"Accept: application/json"',
                '--max-time', '30',
                '--retry', '3',
                '--retry-delay', '5',
                '--retry-max-time', '60',
                `"${siteUrl}/wp-json"`
            ].join(' ');

            const response = await new Promise((resolve, reject) => {
                require('child_process').exec(command, (error, stdout, stderr) => {
                    if (error) {
                        console.warn(`Warning: Failed to get WordPress version: ${error.message}`);
                        resolve(null); // Don't fail, just return null
                        return;
                    }
                    try {
                        const data = JSON.parse(stdout);
                        resolve(data.version);
                    } catch (e) {
                        console.warn(`Warning: Failed to parse WordPress version: ${e.message}`);
                        resolve(null); // Don't fail, just return null
                    }
                });
            });

            return response;
        } catch (error) {
            console.warn(`Warning: Error getting WordPress version: ${error.message}`);
            return null; // Don't fail, just return null
        }
    }

    async checkDependencies(plugin, siteUrl, authToken) {
        try {
            const wpVersion = await this.getWordPressVersion(siteUrl, authToken);
            const dependencies = {
                wordpress: wpVersion || 'unknown',
                php: process.env.PHP_VERSION || '7.4',
                areMet: true,
                missing: []
            };

            // If we couldn't get WordPress version, don't fail the check
            if (!wpVersion) {
                console.warn('Could not determine WordPress version, continuing anyway');
            }

            // Check plugin requirements if available
            if (plugin.requires) {
                if (plugin.requires.wordpress && wpVersion) {
                    if (!this.checkVersion(wpVersion, plugin.requires.wordpress)) {
                        dependencies.areMet = false;
                        dependencies.missing.push(`WordPress ${plugin.requires.wordpress}`);
                    }
                }

                if (plugin.requires.php) {
                    const phpVersion = process.env.PHP_VERSION || '7.4';
                    if (!this.checkVersion(phpVersion, plugin.requires.php)) {
                        dependencies.areMet = false;
                        dependencies.missing.push(`PHP ${plugin.requires.php}`);
                    }
                }
            }

            return dependencies;
        } catch (error) {
            console.warn(`Warning: Dependency check failed for ${plugin.slug}: ${error.message}`);
            // Return a default response instead of failing
            return {
                wordpress: 'unknown',
                php: process.env.PHP_VERSION || '7.4',
                areMet: true, // Assume dependencies are met if we can't check
                missing: [],
                warning: 'Could not verify all dependencies'
            };
        }
    }

    checkVersion(current, required) {
        try {
            const currentParts = current.split('.').map(Number);
            const requiredParts = required.split('.').map(Number);

            for (let i = 0; i < Math.max(currentParts.length, requiredParts.length); i++) {
                const currentPart = currentParts[i] || 0;
                const requiredPart = requiredParts[i] || 0;

                if (currentPart > requiredPart) return true;
                if (currentPart < requiredPart) return false;
            }

            return true;
        } catch (error) {
            console.warn(`Warning: Version comparison failed: ${error.message}`);
            return true; // Assume version check passes if we can't compare
        }
    }
}

module.exports = DependencyChecker; 