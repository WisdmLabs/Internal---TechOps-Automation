/**
 * @deprecated This class is deprecated. Backup functionality has been moved to download-content.sh
 * Will be removed in a future version.
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class BackupManager {
    constructor(baseDir) {
        this.baseDir = baseDir;
        
        // Check if BACKUP_DIR environment variable is set (from download-content.sh)
        if (process.env.BACKUP_DIR) {
            this.backupDir = process.env.BACKUP_DIR;
            console.log(`[DEBUG] Using backup directory from environment: ${this.backupDir}`);
        } else {
            // Fallback to finding repository root and creating backup directory
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
    }

    async createBackup() {
        // Instead of creating a new backup, just return the existing backup directory
        // This is because download-content.sh already created the backup
        console.log(`[DEBUG] Using existing backup directory: ${this.backupDir}`);
        return this.backupDir;
    }

    async restoreBackup(backupPath) {
        // Restore from backup
        console.log(`[DEBUG] Restoring from backup: ${backupPath}`);
        fs.cpSync(
            path.join(backupPath, 'plugins'),
            this.baseDir,
            { recursive: true, force: true }
        );
    }
}

module.exports = BackupManager; 