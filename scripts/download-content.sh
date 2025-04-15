#!/bin/bash

# Exit on any error
set -e

# Enable debug mode
set -x

# Find the repository root by looking for .git directory
find_repo_root() {
    local current_dir="$1"
    local repo_root=""
    
    # Traverse up until we find the .git directory or reach the filesystem root
    while [ "$current_dir" != "/" ]; do
        if [ -d "$current_dir/.git" ]; then
            repo_root="$current_dir"
            break
        fi
        current_dir=$(dirname "$current_dir")
    done
    
    # If we couldn't find the .git directory, use the current directory
    if [ -z "$repo_root" ]; then
        repo_root="$(pwd)"
    fi
    
    echo "$repo_root"
}

# Get the repository root
REPO_ROOT=$(find_repo_root "$(pwd)")
echo "[DEBUG] Repository root: $REPO_ROOT"

# Create a single backup directory for this run
mkdir -p "$REPO_ROOT/_backups"
BACKUP_TIMESTAMP=$(date +%Y-%m-%dT%H-%M-%SZ)
BACKUP_DIR="$REPO_ROOT/_backups/backup-${BACKUP_TIMESTAMP}"
mkdir -p "${BACKUP_DIR}"
echo "[DEBUG] Backup directory: $BACKUP_DIR"

# Function to cleanup temporary files and old backups
cleanup() {
    local exit_code=$?
    echo "Cleaning up temporary files and old backups..."
    find . -name "*.tmp" -type f -delete
    
    # Keep only the latest 3 backups
    if [ -d "$REPO_ROOT/_backups" ]; then
        cd "$REPO_ROOT/_backups"
        ls -t | tail -n +4 | xargs -r rm -rf
        cd "$REPO_ROOT"
    fi
    
    exit $exit_code
}

# Set up cleanup trap
trap cleanup EXIT

# Function to verify copy operation
verify_copy() {
    local source="$1"
    local dest="$2"
    local sync_plugin="techops-content-sync"
    
    # Count files excluding the sync plugin
    local source_count=$(find "$source" -type f ! -path "*/$sync_plugin/*" | wc -l)
    local dest_count=$(find "$dest" -type f ! -path "*/$sync_plugin/*" | wc -l)
    
    echo "Source files (excluding $sync_plugin): $source_count"
    echo "Destination files (excluding $sync_plugin): $dest_count"
    
    if [ "$source_count" -eq "$dest_count" ]; then
        echo "✅ Copy verified: $source_count files copied from $source to $dest (excluding $sync_plugin)"
        return 0
    else
        echo "❌ Copy failed: Expected $source_count files, got $dest_count (excluding $sync_plugin)"
        echo "Source directory contents (excluding $sync_plugin):"
        find "$source" -type f ! -path "*/$sync_plugin/*" -ls
        echo "Destination directory contents (excluding $sync_plugin):"
        find "$dest" -type f ! -path "*/$sync_plugin/*" -ls
        return 1
    fi
}

# Check for required commands
required_commands=(
    "curl"
    "jq"
    "base64"
    "unzip"
)

for cmd in "${required_commands[@]}"; do
    if ! command -v "$cmd" &> /dev/null; then
        echo "Error: Required command '$cmd' is not installed"
        exit 1
    fi
done

# Check for required environment variables
required_vars=(
    "LIVE_SITE_AUTH_TOKEN"
    "STAGING_SITE_AUTH_TOKEN"
    "LIVE_SITE_URL"
    "STAGING_SITE_URL"
)

for var in "${required_vars[@]}"; do
    if [ -z "${!var}" ]; then
        echo "Error: Required environment variable $var is not set"
        exit 1
    fi
done

# Configuration
BASE_DIR="wp-content"
PLUGINS_DIR="$BASE_DIR/plugins"
THEMES_DIR="$BASE_DIR/themes"
SYNC_PLUGIN="techops-content-sync"

# Create backup structure
mkdir -p "${BACKUP_DIR}/plugins"
mkdir -p "${BACKUP_DIR}/themes"
mkdir -p "${BACKUP_DIR}/sync-plugin"

# Function to backup sync plugin
backup_sync_plugin() {
    echo "Backing up sync plugin..."
    if [ -d "$PLUGINS_DIR/$SYNC_PLUGIN" ]; then
        cp -r "$PLUGINS_DIR/$SYNC_PLUGIN" "${BACKUP_DIR}/sync-plugin/"
        echo "✅ Sync plugin backed up successfully"
    else
        echo "⚠️ Sync plugin not found in plugins directory"
    fi
}

# Function to restore sync plugin
restore_sync_plugin() {
    echo "Restoring sync plugin..."
    if [ -d "${BACKUP_DIR}/sync-plugin/$SYNC_PLUGIN" ]; then
        cp -r "${BACKUP_DIR}/sync-plugin/$SYNC_PLUGIN" "$PLUGINS_DIR/"
        echo "✅ Sync plugin restored successfully"
    else
        echo "⚠️ No backup found for sync plugin"
    fi
}

# Function to make API request
make_api_request() {
    local url="$1"
    local auth_token="$2"
    local endpoint="$3"
    local description="$4"
    local output_file=$(mktemp)
    local headers_file=$(mktemp)
    local max_retries=3
    local retry_count=0

    while [ $retry_count -lt $max_retries ]; do
        # Debug output to stderr instead of stdout
        >&2 echo "Making $description API request to: ${url}${endpoint}"
        >&2 echo "Request headers:"
        >&2 echo "Authorization: Basic [REDACTED]"

        # Make the API request and store response
        if curl -s \
            -H "Authorization: Basic ${auth_token}" \
            -H "Accept: application/json" \
            -H "Content-Type: application/json" \
            --max-time 30 \
            "${url}${endpoint}" > "${output_file}"; then
            
            local status_code=$(curl -s -o /dev/null -w "%{http_code}" \
                -H "Authorization: Basic ${auth_token}" \
                -H "Accept: application/json" \
                -H "Content-Type: application/json" \
                --max-time 30 \
                "${url}${endpoint}")

            >&2 echo "Response status code: ${status_code}"
            >&2 echo "Response body:"
            >&2 cat "${output_file}"

            if [ "${status_code}" = "200" ]; then
                # Validate JSON
                if jq empty "${output_file}" 2>/dev/null; then
                    echo "${output_file}"
                    rm -f "${headers_file}"
                    return 0
                else
                    >&2 echo "Error: Invalid JSON response"
                    >&2 cat "${output_file}"
                fi
            elif [ "${status_code}" = "429" ]; then
                >&2 echo "Rate limited. Retrying in 5 seconds..."
                sleep 5
                retry_count=$((retry_count + 1))
                continue
            else
                >&2 echo "Error: $description API request failed with status ${status_code}"
                >&2 echo "Full response:"
                >&2 cat "${output_file}"
            fi
        else
            >&2 echo "Error: Network request failed"
        fi

        retry_count=$((retry_count + 1))
        if [ $retry_count -lt $max_retries ]; then
            >&2 echo "Retrying in 5 seconds... (Attempt $retry_count of $max_retries)"
            sleep 5
        fi
    done

    rm -f "${output_file}" "${headers_file}"
    return 1
}

# Verify auth token format
echo "Verifying auth tokens format..."
for token_var in "LIVE_SITE_AUTH_TOKEN" "STAGING_SITE_AUTH_TOKEN"; do
    if ! echo "${!token_var}" | base64 -d > /dev/null 2>&1; then
        echo "Error: $token_var is not valid base64"
        exit 1
    fi
done

echo "Starting WordPress content sync..."
echo "Using live site URL: $LIVE_SITE_URL"
echo "Using staging site URL: $STAGING_SITE_URL"

# Backup sync plugin before starting the sync process
backup_sync_plugin

# Download and process plugins from live site
echo "Fetching plugins list from live site..."
PLUGINS_RESPONSE_FILE=$(make_api_request "$LIVE_SITE_URL" "$LIVE_SITE_AUTH_TOKEN" "/wp-json/techops/v1/plugins/list" "plugins list")
PLUGINS_EXIT_CODE=$?

if [ ${PLUGINS_EXIT_CODE} -eq 0 ] && [ -f "${PLUGINS_RESPONSE_FILE}" ]; then
    echo "Processing plugins data..."
    WP_AUTH_TOKEN="$LIVE_SITE_AUTH_TOKEN" SITE_URL="$LIVE_SITE_URL" node scripts/process-plugins.js < "${PLUGINS_RESPONSE_FILE}"
    PROCESS_EXIT_CODE=$?
    rm -f "${PLUGINS_RESPONSE_FILE}"
    
    if [ ${PROCESS_EXIT_CODE} -ne 0 ]; then
        echo "Error processing plugins data"
        exit 1
    fi
else
    echo "Error fetching plugins list"
    exit 1
fi

# Download and process themes from live site
echo "Fetching themes list from live site..."
THEMES_RESPONSE_FILE=$(make_api_request "$LIVE_SITE_URL" "$LIVE_SITE_AUTH_TOKEN" "/wp-json/techops/v1/themes/list" "themes list")
THEMES_EXIT_CODE=$?

if [ ${THEMES_EXIT_CODE} -eq 0 ] && [ -f "${THEMES_RESPONSE_FILE}" ]; then
    echo "Processing themes data..."
    WP_AUTH_TOKEN="$LIVE_SITE_AUTH_TOKEN" SITE_URL="$LIVE_SITE_URL" node scripts/process-themes.js < "${THEMES_RESPONSE_FILE}"
    PROCESS_EXIT_CODE=$?
    rm -f "${THEMES_RESPONSE_FILE}"
    
    if [ ${PROCESS_EXIT_CODE} -ne 0 ]; then
        echo "Error processing themes data"
        exit 1
    fi
else
    echo "Error fetching themes list"
    exit 1
fi

# After successful processing, move content to final location
if [ ${PROCESS_EXIT_CODE} -eq 0 ]; then
    echo "Moving processed content to final location..."
    
    # Debug: Show contents of backup directories
    echo "Contents of backup directories before copy:"
    echo "Plugins backup:"
    ls -la "${BACKUP_DIR}/plugins"
    echo "Themes backup:"
    ls -la "${BACKUP_DIR}/themes"
    
    # Clear existing content
    echo "Clearing existing content..."
    rm -rf "$PLUGINS_DIR"/*
    rm -rf "$THEMES_DIR"/*
    
    # Move new content from backup with verification
    if [ -d "${BACKUP_DIR}/plugins" ]; then
        echo "Copying plugins from backup..."
        if ! cp -r "${BACKUP_DIR}/plugins"/* "$PLUGINS_DIR"/; then
            echo "Error: Failed to copy plugins"
            exit 1
        fi
        verify_copy "${BACKUP_DIR}/plugins" "$PLUGINS_DIR"
    fi
    
    if [ -d "${BACKUP_DIR}/themes" ]; then
        echo "Copying themes from backup..."
        if ! cp -r "${BACKUP_DIR}/themes"/* "$THEMES_DIR"/; then
            echo "Error: Failed to copy themes"
            exit 1
        fi
        verify_copy "${BACKUP_DIR}/themes" "$THEMES_DIR"
    fi
    
    # Restore sync plugin
    # restore_sync_plugin
    
    # Verify final content
    echo "Verifying final content in wp-content directories:"
    echo "Plugins directory:"
    ls -la "$PLUGINS_DIR"
    echo "Themes directory:"
    ls -la "$THEMES_DIR"
    
    # Check if directories have content
    if [ ! "$(ls -A "$PLUGINS_DIR")" ] || [ ! "$(ls -A "$THEMES_DIR")" ]; then
        echo "Error: One or more directories are empty after copy"
        exit 1
    fi
fi

echo "Content sync completed successfully!" 