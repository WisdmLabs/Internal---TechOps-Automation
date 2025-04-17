#!/usr/bin/env node

const { Octokit } = require('@octokit/rest');
const VersionChecker = require('./utils/version-checker');
const Logger = require('./utils/logger');
const path = require('path');
const fs = require('fs').promises;

class UpdateChecker {
    constructor() {
        this.logger = new Logger('UpdateChecker');
        this.versionChecker = new VersionChecker();
        this.octokit = new Octokit({
            auth: process.env.GITHUB_TOKEN
        });
        this.configDir = path.join(process.cwd(), 'config');
        this.versionsFile = path.join(this.configDir, 'versions.json');
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

            // Try to read the versions file
            try {
                const data = await fs.readFile(this.versionsFile, 'utf8');
                return JSON.parse(data);
            } catch (error) {
                // If file doesn't exist, create it with default structure
                this.logger.info('Versions file not found. Creating default versions.json...');
                const defaultVersions = { plugins: {}, themes: {} };
                await fs.writeFile(this.versionsFile, JSON.stringify(defaultVersions, null, 2));
                return defaultVersions;
            }
        } catch (error) {
            this.logger.error(`Error handling versions file: ${error.message}`);
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
                themes: {}
            };

            if (checkType === 'all' || checkType === 'plugins') {
                for (const [slug, version] of Object.entries(versions.plugins)) {
                    const updateInfo = await this.versionChecker.checkPluginUpdates(slug, version);
                    if (updateInfo && updateInfo.hasUpdate) {
                        updates.plugins[slug] = updateInfo;
                    }
                }
            }

            if (checkType === 'all' || checkType === 'themes') {
                for (const [slug, version] of Object.entries(versions.themes)) {
                    const updateInfo = await this.versionChecker.checkThemeUpdates(slug, version);
                    if (updateInfo && updateInfo.hasUpdate) {
                        updates.themes[slug] = updateInfo;
                    }
                }
            }

            const hasUpdates = Object.keys(updates.plugins).length > 0 || Object.keys(updates.themes).length > 0;
            
            if (hasUpdates) {
                const [owner, repo] = process.env.GITHUB_REPOSITORY.split('/');
                const issueBody = await this.generateIssueBody(updates);
                const title = `WordPress Updates Available - ${new Date().toISOString().split('T')[0]}`;
                await this.createGitHubIssue(this.octokit, owner, repo, title, issueBody);
            } else {
                this.logger.info('No updates available');
            }

            return updates;
        } catch (error) {
            this.logger.error('Error:', error.message);
            return { plugins: {}, themes: {} };
        }
    }
}

// If running directly (not imported as a module)
if (require.main === module) {
    const checker = new UpdateChecker();
    const checkType = process.argv[2] || 'all';
    
    checker.checkUpdates(checkType)
        .then(updates => {
            process.exit(Object.keys(updates.plugins).length > 0 || Object.keys(updates.themes).length > 0 ? 1 : 0);
        })
        .catch(error => {
            console.error('Error:', error);
            process.exit(1);
        });
}

module.exports = UpdateChecker; 