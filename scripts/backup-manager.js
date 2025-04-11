const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class BackupManager {
    constructor(baseDir) {
        this.baseDir = baseDir;
        this.backupDir = path.join(baseDir, '_backups');
    }

    async createBackup() {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const backupPath = path.join(this.backupDir, `backup-${timestamp}`);
        
        // Create backup directory
        fs.mkdirSync(backupPath, { recursive: true });
        
        // Copy plugins and themes
        fs.cpSync(
            path.join(this.baseDir, 'plugins'),
            path.join(backupPath, 'plugins'),
            { recursive: true }
        );
        
        fs.cpSync(
            path.join(this.baseDir, 'themes'),
            path.join(backupPath, 'themes'),
            { recursive: true }
        );
        
        return backupPath;
    }

    async restoreBackup(backupPath) {
        // Restore from backup
        fs.cpSync(
            path.join(backupPath, 'plugins'),
            path.join(this.baseDir, 'plugins'),
            { recursive: true, force: true }
        );
        
        fs.cpSync(
            path.join(backupPath, 'themes'),
            path.join(this.baseDir, 'themes'),
            { recursive: true, force: true }
        );
    }
}

module.exports = BackupManager; 