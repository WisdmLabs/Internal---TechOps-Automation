const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class BackupManager {
    constructor(baseDir) {
        this.baseDir = baseDir;
        // Create backups directory in the repository root
        // Find the repository root by looking for .git directory
        let currentDir = path.dirname(baseDir);
        let repoRoot = null;
        
        // Traverse up until we find the .git directory or reach the filesystem root
        while (currentDir !== path.dirname(currentDir)) {
            if (fs.existsSync(path.join(currentDir, '.git'))) {
                repoRoot = currentDir;
                break;
            }
            currentDir = path.dirname(currentDir);
        }
        
        // If we couldn't find the .git directory, use the current directory
        if (!repoRoot) {
            repoRoot = process.cwd();
        }
        
        this.backupDir = path.join(repoRoot, '_backups');
        console.log(`[DEBUG] Repository root: ${repoRoot}`);
        console.log(`[DEBUG] Backup directory: ${this.backupDir}`);
    }

    async createBackup() {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const backupPath = path.join(this.backupDir, `backup-${timestamp}`);
        
        // Create backup directory
        fs.mkdirSync(backupPath, { recursive: true });
        
        // Copy the entire plugins directory
        fs.cpSync(
            this.baseDir,
            path.join(backupPath, 'plugins'),
            { recursive: true }
        );
        
        return backupPath;
    }

    async restoreBackup(backupPath) {
        // Restore from backup
        fs.cpSync(
            path.join(backupPath, 'plugins'),
            this.baseDir,
            { recursive: true, force: true }
        );
    }
}

module.exports = BackupManager; 