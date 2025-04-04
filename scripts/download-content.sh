#!/bin/bash

# Exit on any error
set -e

# Enable debug mode
set -x

# Set AUTH_TOKEN from WP_AUTH_TOKEN
AUTH_TOKEN="${WP_AUTH_TOKEN}"

# Configuration
BASE_DIR="wp-content"
PLUGINS_DIR="$BASE_DIR/plugins"
THEMES_DIR="$BASE_DIR/themes"

# Function to make API request
make_api_request() {
    local endpoint="$1"
    local description="$2"
    local output_file=$(mktemp)
    local headers_file=$(mktemp)

    # Debug output to stderr instead of stdout
    >&2 echo "Making $description API request to: ${SITE_URL}${endpoint}"
    >&2 echo "Request headers:"
    >&2 echo "Authorization: Basic [REDACTED]"

    # Make the API request and store response
    curl -s \
        -H "Authorization: Basic ${AUTH_TOKEN}" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        "${SITE_URL}${endpoint}" > "${output_file}"
    
    local status_code=$(curl -s -o /dev/null -w "%{http_code}" \
        -H "Authorization: Basic ${AUTH_TOKEN}" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        "${SITE_URL}${endpoint}")

    >&2 echo "Response status code: ${status_code}"
    >&2 echo "Response body:"
    >&2 cat "${output_file}"

    if [ $? -ne 0 ] || [ "${status_code}" != "200" ]; then
        >&2 echo "Error: $description API request failed with status ${status_code}"
        >&2 echo "Full response:"
        >&2 cat "${output_file}"
        rm -f "${output_file}" "${headers_file}"
        return 1
    fi

    # Validate JSON
    if ! jq empty "${output_file}" 2>/dev/null; then
        >&2 echo "Error: Invalid JSON response"
        >&2 cat "${output_file}"
        rm -f "${output_file}" "${headers_file}"
        return 1
    fi

    # Only output the file path to stdout
    echo "${output_file}"
    rm -f "${headers_file}"
}

# Ensure directories exist
echo "Creating directory structure..."
mkdir -p "$PLUGINS_DIR"
mkdir -p "$THEMES_DIR"

# Authentication checks
if [ -z "$WP_AUTH_TOKEN" ]; then
    echo "Error: WP_AUTH_TOKEN is not set"
    exit 1
fi

if [ -z "$SITE_URL" ]; then
    echo "Error: SITE_URL is not set"
    exit 1
fi

# Verify auth token format
echo "Verifying auth token format..."
if ! echo "$WP_AUTH_TOKEN" | base64 -d > /dev/null 2>&1; then
    echo "Error: WP_AUTH_TOKEN is not valid base64"
    exit 1
fi

echo "Starting WordPress content sync..."
echo "Using site URL: $SITE_URL"

# Download and process plugins
echo "Fetching plugins list..."
PLUGINS_RESPONSE_FILE=$(make_api_request "/wp-json/techops/v1/plugins/list" "plugins list")
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

# Download and process themes
echo "Fetching themes list..."
THEMES_RESPONSE_FILE=$(make_api_request "/wp-json/techops/v1/themes/list" "themes list")
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