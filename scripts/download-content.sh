#!/bin/bash

# Exit on any error
set -e

# Enable debug mode
set -x

# Function to cleanup temporary files
cleanup() {
    local exit_code=$?
    echo "Cleaning up temporary files..."
    find . -name "*.tmp" -type f -delete
    exit $exit_code
}

# Set up cleanup trap
trap cleanup EXIT

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

# Ensure directories exist
echo "Creating directory structure..."
mkdir -p "$PLUGINS_DIR"
mkdir -p "$THEMES_DIR"

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

# Download and process plugins from live site
echo "Fetching plugins list from live site..."
PLUGINS_RESPONSE_FILE=$(make_api_request "$LIVE_SITE_URL" "$LIVE_SITE_AUTH_TOKEN" "/wp-json/techops/v1/plugins/list" "plugins list")
PLUGINS_EXIT_CODE=$?

if [ ${PLUGINS_EXIT_CODE} -eq 0 ] && [ -f "${PLUGINS_RESPONSE_FILE}" ]; then
    echo "Processing plugins data..."
    node scripts/process-plugins.js < "${PLUGINS_RESPONSE_FILE}"
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
    node scripts/process-themes.js < "${THEMES_RESPONSE_FILE}"
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

echo "Content sync completed successfully!" 