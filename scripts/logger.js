const fs = require('fs');
const path = require('path');

class Logger {
    constructor(logFile) {
        this.logFile = logFile;
        this.ensureLogDirectory();
    }

    ensureLogDirectory() {
        const logDir = path.dirname(this.logFile);
        if (!fs.existsSync(logDir)) {
            fs.mkdirSync(logDir, { recursive: true });
        }
    }

    log(level, message, details = {}) {
        const logEntry = {
            timestamp: new Date().toISOString(),
            level,
            message,
            details
        };

        // Write to file
        fs.appendFileSync(
            this.logFile,
            JSON.stringify(logEntry) + '\n'
        );

        // Console output with color
        const colors = {
            error: '\x1b[31m', // Red
            warn: '\x1b[33m',  // Yellow
            info: '\x1b[36m',  // Cyan
            debug: '\x1b[35m'  // Magenta
        };
        const reset = '\x1b[0m';

        console.log(`${colors[level] || ''}[${level.toUpperCase()}] ${message}${reset}`);
        if (Object.keys(details).length > 0) {
            console.log(JSON.stringify(details, null, 2));
        }
    }

    error(message, details = {}) {
        this.log('error', message, details);
    }

    warn(message, details = {}) {
        this.log('warn', message, details);
    }

    info(message, details = {}) {
        this.log('info', message, details);
    }

    debug(message, details = {}) {
        this.log('debug', message, details);
    }
}

module.exports = Logger; 