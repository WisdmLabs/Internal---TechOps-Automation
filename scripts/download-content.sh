#!/bin/bash

# Exit on any error
set -e

# Enable debug mode
set -x

# Setup logging
LOG_FILE="$(pwd)/sync-$(date +%Y%m%d-%H%M%S).log"
exec 1> >(tee -a "$LOG_FILE")
exec 2> >(tee -a "$LOG_FILE" >&2)

# Setup status tracking
STATUS_FILE="$(pwd)/.sync-status"
echo "STARTED:$(date +%s)" > "$STATUS_FILE"
trap 'echo "FAILED:$(date +%s)" > "$STATUS_FILE"' EXIT

# Process cleanup function
cleanup_processes() {
    local pids=$(pgrep -f "process-(plugins|themes).js")
    if [ ! -z "$pids" ]; then
        echo "Cleaning up remaining processes..."
        kill $pids 2>/dev/null || true
    fi
}
trap cleanup_processes EXIT

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

# Export the backup directory path for use by other scripts
export BACKUP_DIR="${BACKUP_DIR}"

# Parse command-line arguments
PLUGIN_SLUG=""
THEME_SLUG=""

while [[ $# -gt 0 ]]; do
    case "$1" in
        --plugin=*)
            PLUGIN_SLUG="${1#*=}"
            shift
            ;;
        --theme=*)
            THEME_SLUG="${1#*=}"
            shift
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

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
    
    # Update status file based on exit code
    if [ $exit_code -eq 0 ]; then
        echo "COMPLETED:$(date +%s)" > "$STATUS_FILE"
    else
        echo "FAILED:$(date +%s)" > "$STATUS_FILE"
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

# Function to backup existing content
backup_existing_content() {
    local content_type="$1"  # "plugins" or "themes"
    local source_dir="$2"    # "$PLUGINS_DIR" or "$THEMES_DIR"
    local specific_slug="$3" # Optional: specific plugin or theme slug to backup
    
    echo "Backing up existing $content_type..."
    
    if [ -z "$specific_slug" ]; then
        # Backup entire content type
        if [ -d "$source_dir" ] && [ "$(ls -A "$source_dir")" ]; then
            echo "Backing up all $content_type..."
            cp -r "$source_dir"/* "${BACKUP_DIR}/$content_type/"
            echo "✅ All $content_type backed up successfully"
        else
            echo "⚠️ No existing $content_type to backup"
        fi
    else
        # Backup specific plugin or theme
        local source_path="$source_dir/$specific_slug"
        local backup_path="${BACKUP_DIR}/$content_type/$specific_slug"
        
        if [ -d "$source_path" ]; then
            echo "Backing up specific $content_type: $specific_slug..."
            cp -r "$source_path" "${BACKUP_DIR}/$content_type/"
            echo "✅ $content_type '$specific_slug' backed up successfully"
        else
            echo "⚠️ No existing $content_type '$specific_slug' to backup"
        fi
    fi
}

# Function to restore from backup
restore_from_backup() {
    local content_type="$1"  # "plugins" or "themes"
    local target_dir="$2"    # "$PLUGINS_DIR" or "$THEMES_DIR"
    local specific_slug="$3" # Optional: specific plugin or theme slug to restore
    
    echo "Restoring $content_type from backup..."
    
    if [ -z "$specific_slug" ]; then
        # Restore entire content type
        if [ -d "${BACKUP_DIR}/$content_type" ] && [ "$(ls -A "${BACKUP_DIR}/$content_type")" ]; then
            echo "Restoring all $content_type..."
            rm -rf "$target_dir"/*
            cp -r "${BACKUP_DIR}/$content_type"/* "$target_dir"/
            echo "✅ All $content_type restored successfully"
            return 0
        else
            echo "⚠️ No backup found for $content_type"
            return 1
        fi
    else
        # Restore specific plugin or theme
        local backup_path="${BACKUP_DIR}/$content_type/$specific_slug"
        local target_path="$target_dir/$specific_slug"
        
        if [ -d "$backup_path" ]; then
            echo "Restoring specific $content_type: $specific_slug..."
            if [ -d "$target_path" ]; then
                rm -rf "$target_path"
            fi
            cp -r "$backup_path" "$target_dir/"
            echo "✅ $content_type '$specific_slug' restored successfully"
            return 0
        else
            echo "⚠️ No backup found for $content_type '$specific_slug'"
            return 1
        fi
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

# Additional environment variable checks
if [ -z "$BACKUP_DIR" ]; then
    echo "Error: BACKUP_DIR environment variable is not set"
    exit 1
fi

if [ ! -d "$BACKUP_DIR" ]; then
    echo "Error: BACKUP_DIR directory does not exist: $BACKUP_DIR"
    exit 1
fi

# Check directory permissions
if [ ! -w "$PLUGINS_DIR" ] || [ ! -w "$THEMES_DIR" ]; then
    echo "Error: Insufficient permissions to write to plugins or themes directories"
    exit 1
fi

# Check disk space
required_space=1024  # 1GB in MB
available_space=$(df -m . | awk 'NR==2 {print $4}')
if [ "$available_space" -lt "$required_space" ]; then
    echo "Error: Insufficient disk space. Required: ${required_space}MB, Available: ${available_space}MB"
    exit 1
fi

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

# Function to verify checksum
verify_checksum() {
    local file="$1"
    local expected_checksum="$2"
    local actual_checksum=$(sha256sum "$file" | cut -d' ' -f1)
    if [ "$actual_checksum" != "$expected_checksum" ]; then
        echo "Error: Checksum verification failed for $file"
        return 1
    fi
    return 0
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

# Backup existing content before processing
backup_existing_content "plugins" "$PLUGINS_DIR" "$PLUGIN_SLUG"
backup_existing_content "themes" "$THEMES_DIR" "$THEME_SLUG"

# Backup sync plugin before starting the sync process
backup_sync_plugin

# Download and process plugins from live site
echo "Fetching plugins list from live site..."
PLUGINS_RESPONSE_FILE=$(make_api_request "$LIVE_SITE_URL" "$LIVE_SITE_AUTH_TOKEN" "/wp-json/techops/v1/plugins/list" "plugins list")
PLUGINS_EXIT_CODE=$?

if [ ${PLUGINS_EXIT_CODE} -eq 0 ] && [ -f "${PLUGINS_RESPONSE_FILE}" ]; then
    echo "Processing plugins data..."
    WP_AUTH_TOKEN="$LIVE_SITE_AUTH_TOKEN" SITE_URL="$LIVE_SITE_URL" BACKUP_DIR="${BACKUP_DIR}" PLUGIN_SLUG="$PLUGIN_SLUG" node scripts/process-plugins.js < "${PLUGINS_RESPONSE_FILE}"
    PROCESS_EXIT_CODE=$?
    rm -f "${PLUGINS_RESPONSE_FILE}"
    
    if [ ${PROCESS_EXIT_CODE} -ne 0 ]; then
        echo "Error processing plugins data"
        
        # Check if a specific plugin slug was provided
        if [ -n "$PLUGIN_SLUG" ]; then
            restore_from_backup "plugins" "$PLUGINS_DIR" "$PLUGIN_SLUG"
        else
            restore_from_backup "plugins" "$PLUGINS_DIR"
        fi
        
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
    WP_AUTH_TOKEN="$LIVE_SITE_AUTH_TOKEN" SITE_URL="$LIVE_SITE_URL" BACKUP_DIR="${BACKUP_DIR}" THEME_SLUG="$THEME_SLUG" node scripts/process-themes.js < "${THEMES_RESPONSE_FILE}"
    PROCESS_EXIT_CODE=$?
    rm -f "${THEMES_RESPONSE_FILE}"
    
    if [ ${PROCESS_EXIT_CODE} -ne 0 ]; then
        echo "Error processing themes data"
        
        # Check if a specific theme slug was provided
        if [ -n "$THEME_SLUG" ]; then
            restore_from_backup "themes" "$THEMES_DIR" "$THEME_SLUG"
        else
            restore_from_backup "themes" "$THEMES_DIR"
        fi
        
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
    if [ -d "${BACKUP_DIR}/plugins" ] && [ "$(ls -A "${BACKUP_DIR}/plugins")" ]; then
        echo "Copying plugins from backup..."
        if ! cp -r "${BACKUP_DIR}/plugins"/* "$PLUGINS_DIR"/; then
            echo "Error: Failed to copy plugins"
            exit 1
        fi
        verify_copy "${BACKUP_DIR}/plugins" "$PLUGINS_DIR"
    else
        echo "Error: Plugins backup directory is empty or does not exist"
        exit 1
    fi
    
    if [ -d "${BACKUP_DIR}/themes" ] && [ "$(ls -A "${BACKUP_DIR}/themes")" ]; then
        echo "Copying themes from backup..."
        if ! cp -r "${BACKUP_DIR}/themes"/* "$THEMES_DIR"/; then
            echo "Error: Failed to copy themes"
            exit 1
        fi
        verify_copy "${BACKUP_DIR}/themes" "$THEMES_DIR"
    else
        echo "Error: Themes backup directory is empty or does not exist"
        exit 1
    fi
    
    # Restore sync plugin
    restore_sync_plugin
    
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