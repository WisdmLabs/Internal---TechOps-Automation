# TechOps Git Automation Project

This project provides an automated solution for syncing WordPress plugins and themes across multiple sites using GitHub Actions. It consists of two main components:

## Project Structure

```
TECHOPS-GIT-AUTOMATION/
├── Internal---TechOps-Automation/    # GitHub Actions automation repository
│   ├── .github/
│   │   └── workflows/               # GitHub Actions workflow definitions
│   ├── scripts/                     # Node.js processing scripts
│   └── wp-content/                  # Synchronized content storage
└── techops-content-sync/            # WordPress plugin for content sync
    ├── includes/                    # Plugin core functionality
    └── techops-content-sync.php     # Main plugin file
```

## Components

### 1. WordPress Plugin (techops-content-sync)

The `techops-content-sync` plugin provides secure REST API endpoints for downloading plugins and themes from WordPress sites.

#### Key Features:
- Secure REST API endpoints with Application Password authentication
- Rate limiting for API requests
- Safe file handling and ZIP creation
- Proper error handling and logging

#### API Endpoints:
- `GET /wp-json/techops/v1/plugins/list` - List all installed plugins
- `GET /wp-json/techops/v1/themes/list` - List all installed themes
- `GET /wp-json/techops/v1/plugins/download/{slug}` - Download specific plugin
- `GET /wp-json/techops/v1/themes/download/{slug}` - Download specific theme

### 2. GitHub Actions Automation (Internal---TechOps-Automation)

This repository contains the automation scripts and workflows for syncing content between WordPress sites.

#### Key Components:

1. **GitHub Actions Workflow** (`.github/workflows/wordpress-content-sync.yml`)
   - Triggered manually or on schedule
   - Handles authentication and API requests
   - Manages content synchronization process
   - Creates Pull Requests for review

2. **Processing Scripts** (`scripts/`)
   - `download-content.sh`: Main script for downloading content
   - `process-plugins.js`: Handles plugin ZIP processing
   - `process-themes.js`: Handles theme ZIP processing

#### Features:
- Secure credential handling using GitHub Secrets
- Proper error handling and validation
- Clean directory structure maintenance
- Automated cleanup of temporary files

## Setup Instructions

### 1. WordPress Site Setup

1. Install and activate the `techops-content-sync` plugin
2. Generate an Application Password:
   - Go to Users → Your Profile
   - Scroll to "Application Passwords"
   - Enter "GitHub Actions" as the name
   - Copy the generated password

### 2. GitHub Repository Setup

1. Fork/Clone the `Internal---TechOps-Automation` repository
2. Configure GitHub Secrets:
   - `WP_APP_USERNAME`: WordPress username
   - `WP_APP_PASSWORD`: Generated Application Password
   - `WP_AUTH_TOKEN`: Base64 encoded "username:password"

### 3. Running the Workflow

1. Go to Actions tab in GitHub
2. Select "WordPress Content Sync"
3. Click "Run workflow"
4. Enter the WordPress site URL
5. Monitor the execution

## Technical Details

### Authentication Flow
1. WordPress Application Password generates credentials
2. Credentials stored as GitHub Secrets
3. Base64 encoded auth token used in API requests

### Content Sync Process
1. Fetch lists of plugins/themes from WordPress
2. Download ZIP files for each item
3. Extract to temporary directory
4. Process and organize files
5. Clean up temporary files
6. Create Pull Request with changes

### Error Handling
- API request validation
- JSON response verification
- ZIP extraction error handling
- Directory structure validation
- Proper cleanup on failures

## Security Considerations

1. **Authentication**
   - Application Passwords for secure access
   - No permanent API keys stored
   - Credentials managed via GitHub Secrets

2. **File Handling**
   - Path traversal prevention
   - Safe ZIP handling
   - Temporary file cleanup

3. **API Security**
   - Rate limiting
   - Request validation
   - Error logging

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a Pull Request

## License

Both components are licensed under GPL v2 or later. 