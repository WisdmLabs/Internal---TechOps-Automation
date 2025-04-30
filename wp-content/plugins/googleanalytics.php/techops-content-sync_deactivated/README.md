# TechOps Content Sync

A WordPress plugin for syncing plugins and themes with Git repositories.

## Features

- Install plugins and themes directly from Git repositories
- Support for GitHub, GitLab, and Bitbucket repositories
- Branch and tag selection
- Installation history tracking
- Rate limiting for API requests
- Comprehensive error handling and logging
- Modern admin interface with progress indicators

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher
- Git installed on the server
- PHP ZipArchive extension
- Composer dependencies installed

## Installation

1. Download the plugin zip file
2. Upload it to your WordPress site through the plugin installer
3. Activate the plugin
4. Run `composer install` in the plugin directory

## Usage

### Installing from Git

1. Go to the TechOps Sync admin page
2. Enter the Git repository URL
3. Specify the folder path within the repository
4. (Optional) Select a specific branch or tag
5. Choose whether to install as a plugin or theme
6. Click "Install from Git"

### REST API Endpoints

The plugin provides the following REST API endpoints:

#### Git Operations

- `POST /techops/v1/git/download`
  - Install a plugin or theme from a Git repository
  - Parameters:
    - `repo_url` (required): Git repository URL
    - `folder_path` (required): Path to the folder within the repository
    - `branch_or_tag` (optional): Specific branch or tag to install
    - `type` (optional): Installation type ('plugin' or 'theme', defaults to 'plugin')

- `POST /techops/v1/git/branches-tags`
  - Get available branches and tags from a repository
  - Parameters:
    - `repo_url` (required): Git repository URL

#### Plugin Operations

- `GET /techops/v1/plugins/list`
  - List all installed plugins

- `POST /techops/v1/plugins/activate`
  - Activate a plugin
  - Parameters:
    - `plugin` (required): Plugin slug

- `POST /techops/v1/plugins/deactivate`
  - Deactivate a plugin
  - Parameters:
    - `plugin` (required): Plugin slug

- `GET /techops/v1/plugins/download`
  - Download a plugin
  - Parameters:
    - `slug` (required): Plugin slug

#### Theme Operations

- `GET /techops/v1/themes/list`
  - List all installed themes

- `POST /techops/v1/themes/activate`
  - Activate a theme
  - Parameters:
    - `theme` (required): Theme slug

- `POST /techops/v1/themes/deactivate`
  - Deactivate a theme
  - Parameters:
    - `theme` (required): Theme slug

- `GET /techops/v1/themes/download`
  - Download a theme
  - Parameters:
    - `slug` (required): Theme slug

### Authentication

All API endpoints require authentication using Basic Auth. The user must have the `manage_options` capability.

## Security

- All Git repository URLs are validated against allowed domains
- Rate limiting is implemented to prevent abuse
- User inputs are properly sanitized
- Temporary files are cleaned up after operations
- All operations are logged for auditing

## Error Handling

The plugin provides comprehensive error handling:

- Invalid repository URLs
- Missing or invalid folder paths
- Git operation failures
- Installation failures
- Permission issues
- Rate limit exceeded

## Logging

All operations are logged to a file located at:
`wp-content/uploads/techops-content-sync/techops-content-sync.log`

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please open an issue in the GitHub repository. 