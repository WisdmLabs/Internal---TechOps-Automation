#!/usr/bin/env node

const { Octokit } = require('@octokit/rest');
const VersionChecker = require('./utils/version-checker');
const Logger = require('./utils/logger');
const path = require('path');
const fs = require('fs').promises;

class UpdateChecker {
    constructor() {
        this.logger = new Logger('UpdateChecker', { stream: process.stderr });
        this.versionChecker = new VersionChecker();
        
        // Get environment variables
        const githubToken = process.env.GITHUB_TOKEN;
        if (!githubToken) {
            this.logger.error('Missing required environment variable: GITHUB_TOKEN');
            process.exit(1);
        }
        
        this.octokit = new Octokit({
            auth: githubToken
        });
        
        this.configDir = path.join(process.cwd(), 'config');
        this.versionsFile = path.join(this.configDir, 'versions.json');
    }

    async checkDirectoryPermissions(dirPath) {
        try {
            // Check if directory exists
            try {
                await fs.access(dirPath);
                this.logger.info(`Directory exists: ${dirPath}`);
            } catch (error) {
                this.logger.info(`Directory does not exist: ${dirPath}`);
                return true; // Directory doesn't exist, so we can create it
            }

            // Try to write a test file
            const testFile = path.join(dirPath, '.write-test');
            try {
                await fs.writeFile(testFile, 'test');
                await fs.unlink(testFile);
                this.logger.info(`Write permissions verified for: ${dirPath}`);
                return true;
            } catch (error) {
                this.logger.error(`No write permissions for directory: ${dirPath}`);
                this.logger.error(`Error: ${error.message}`);
                return false;
            }
        } catch (error) {
            this.logger.error(`Error checking directory permissions: ${error.message}`);
            return false;
        }
    }

    async loadVersions() {
        try {
            this.logger.info(`Current working directory: ${process.cwd()}`);
            this.logger.info(`Config directory path: ${this.configDir}`);
            this.logger.info(`Versions file path: ${this.versionsFile}`);

            // Check parent directory permissions
            const parentDir = path.dirname(this.configDir);
            const hasPermissions = await this.checkDirectoryPermissions(parentDir);
            if (!hasPermissions) {
                throw new Error(`No write permissions in parent directory: ${parentDir}`);
            }

            // Create config directory if it doesn't exist
            try {
                await fs.access(this.configDir);
                this.logger.info('Config directory exists');
            } catch (error) {
                this.logger.info('Config directory not found. Creating it...');
                try {
                    await fs.mkdir(this.configDir, { recursive: true });
                    this.logger.info(`Successfully created config directory at: ${this.configDir}`);
                } catch (mkdirError) {
                    this.logger.error(`Failed to create config directory: ${mkdirError.message}`);
                    throw mkdirError;
                }
            }

            // Verify config directory permissions
            const configDirPermissions = await this.checkDirectoryPermissions(this.configDir);
            if (!configDirPermissions) {
                throw new Error(`No write permissions in config directory: ${this.configDir}`);
            }

            // Try to read the versions file or fetch from WordPress
            try {
                const data = await fs.readFile(this.versionsFile, 'utf8');
                this.logger.info('Successfully read versions file');
                return JSON.parse(data);
            } catch (error) {
                if (error.code === 'ENOENT') {
                    this.logger.info('Versions file not found. Fetching from WordPress...');
                    const versions = await this.versionChecker.fetchInstalledVersions();
                    await fs.writeFile(this.versionsFile, JSON.stringify(versions, null, 2));
                    this.logger.info(`Successfully created versions file at: ${this.versionsFile}`);
                    return versions;
                }
                this.logger.error(`Error reading versions file: ${error.message}`);
                throw error;
            }
        } catch (error) {
            this.logger.error(`Error handling versions file: ${error.message}`);
            this.logger.error(`Stack trace: ${error.stack}`);
            return { plugins: {}, themes: {} };
        }
    }

    async generateIssueBody(updates) {
        let body = '## WordPress Updates Available\n\n';
        
        if (Object.keys(updates.plugins).length > 0) {
            body += '### Plugins\n\n';
            body += '| Plugin | Current Version | Latest Version | Requires | Last Updated |\n';
            body += '|--------|----------------|----------------|----------|--------------|\n';
            
            for (const [slug, info] of Object.entries(updates.plugins)) {
                if (info.hasUpdate) {
                    body += `| ${slug} | ${info.currentVersion} | ${info.latestVersion} | ${info.requires} | ${info.lastUpdated} |\n`;
                }
            }
        }
        
        if (Object.keys(updates.themes).length > 0) {
            body += '\n### Themes\n\n';
            body += '| Theme | Current Version | Latest Version | Requires | Last Updated |\n';
            body += '|-------|----------------|----------------|----------|--------------|\n';
            
            for (const [slug, info] of Object.entries(updates.themes)) {
                if (info.hasUpdate) {
                    body += `| ${slug} | ${info.currentVersion} | ${info.latestVersion} | ${info.requires} | ${info.lastUpdated} |\n`;
                }
            }
        }
        
        return body;
    }

    async createGitHubIssue(octokit, owner, repo, title, body) {
        try {
            const response = await octokit.issues.create({
                owner,
                repo,
                title,
                body,
                labels: ['wordpress-updates']
            });
            this.logger.info(`Created issue #${response.data.number}`);
            return response.data.number;
        } catch (error) {
            this.logger.error('Error creating GitHub issue:', error.message);
            throw error;
        }
    }

    async checkUpdates(checkType = 'all') {
        try {
            const versions = await this.loadVersions();
            const updates = {
                plugins: {},
                themes: {},
                timestamp: new Date().toISOString()
            };

            if (checkType === 'all' || checkType === 'plugins') {
                for (const [slug, version] of Object.entries(versions.plugins)) {
                    const updateInfo = await this.versionChecker.checkPluginUpdates(slug, version);
                    updates.plugins[slug] = updateInfo || {
                        currentVersion: version,
                        latestVersion: version,
                        hasUpdate: false
                    };
                }
            }

            if (checkType === 'all' || checkType === 'themes') {
                for (const [slug, version] of Object.entries(versions.themes)) {
                    const updateInfo = await this.versionChecker.checkThemeUpdates(slug, version);
                    updates.themes[slug] = updateInfo || {
                        currentVersion: version,
                        latestVersion: version,
                        hasUpdate: false
                    };
                }
            }

            const hasUpdates = Object.values(updates.plugins).some(p => p.hasUpdate) || 
                             Object.values(updates.themes).some(t => t.hasUpdate);
            
            if (hasUpdates) {
                if (!process.env.GITHUB_REPOSITORY) {
                    this.logger.error('Missing GITHUB_REPOSITORY environment variable');
                    return updates;
                }
                
                const [owner, repo] = process.env.GITHUB_REPOSITORY.split('/');
                const issueBody = await this.generateIssueBody(updates);
                const title = `WordPress Updates Available - ${new Date().toISOString().split('T')[0]}`;
                await this.createGitHubIssue(this.octokit, owner, repo, title, issueBody);
            } else {
                this.logger.info('No updates available');
            }

            return updates;
        } catch (error) {
            this.logger.error(`Error checking updates: ${error.message}`);
            throw error;
        }
    }
}

// Main execution
async function main() {
    try {
        const checker = new UpdateChecker();
        const checkType = process.env.CHECK_TYPE || 'all';
        const updates = await checker.checkUpdates(checkType);
        
        // Ensure the updates object is valid
        if (!updates || typeof updates !== 'object') {
            throw new Error('Invalid updates object generated');
        }
        
        // Validate required properties
        if (!updates.plugins || !updates.themes || !updates.timestamp) {
            throw new Error('Missing required properties in updates object');
        }
        
        // Test JSON stringification before output
        try {
            JSON.stringify(updates, null, 2);
        } catch (e) {
            throw new Error(`Failed to stringify updates object: ${e.message}`);
        }
        
        // Output only the JSON to stdout, ensuring no other output
        process.stdout.write(JSON.stringify(updates, null, 2));
    } catch (error) {
        // Write error to stderr
        process.stderr.write(`Error running update checker: ${error.message}\n`);
        if (error.stack) {
            process.stderr.write(`${error.stack}\n`);
        }
        process.exit(1);
    }
}

// Run the main function
if (require.main === module) {
    main();
}

module.exports = UpdateChecker; 