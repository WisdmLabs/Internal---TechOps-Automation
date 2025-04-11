const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class BackupManager {
    constructor(baseDir) {
        this.baseDir = baseDir;
        // Create backups directory at the same level as wp-content
        this.backupDir = path.join(path.dirname(path.dirname(baseDir)), '_backups');
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