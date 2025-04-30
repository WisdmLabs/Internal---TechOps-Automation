const fs = require('fs');
const path = require('path');

class Logger {
    constructor(name, options = {}) {
        this.name = name;
        this.stream = options.stream || process.stdout;
        this.logDir = path.join(process.cwd(), 'logs');
        this.logFile = path.join(this.logDir, `${name}.log`);
        this.ensureLogDirectory();
    }

    ensureLogDirectory() {
        if (!fs.existsSync(this.logDir)) {
            fs.mkdirSync(this.logDir, { recursive: true });
        }
    }

    log(level, message, details = {}) {
        const timestamp = new Date().toISOString();
        const logEntry = {
            timestamp,
            level,
            name: this.name,
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

        // Write to specified stream instead of console.log
        this.stream.write(`${colors[level] || ''}[${level.toUpperCase()}] ${message}${reset}\n`);
        if (Object.keys(details).length > 0) {
            this.stream.write(JSON.stringify(details, null, 2) + '\n');
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