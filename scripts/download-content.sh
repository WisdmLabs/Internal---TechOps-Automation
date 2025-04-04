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

    echo "Making $description API request to: ${SITE_URL}${endpoint}"
    echo "Request headers:"
    echo "Authorization: Basic [REDACTED]"

    # Store response in output file and capture status code separately
    local response=$(curl -s -H "Authorization: Basic ${AUTH_TOKEN}" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        "${SITE_URL}${endpoint}")
    local status_code=$(curl -s -o /dev/null -w "%{http_code}" -H "Authorization: Basic ${AUTH_TOKEN}" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        "${SITE_URL}${endpoint}")

    echo "Response status code: ${status_code}"
    echo "Response headers:"
    cat "${headers_file}"
    echo "Response body:"
    echo "${response}"

    if [ $? -ne 0 ] || [ "${status_code}" != "200" ]; then
        echo "Error: $description API request failed with status ${status_code}"
        echo "Full response:"
        echo "${response}"
        rm -f "${output_file}" "${headers_file}"
        return 1
    fi

    # Write only the JSON response to the output file
    echo "${response}" > "${output_file}"
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

if [ $? -eq 0 ]; then
    echo "Processing plugins data..."
    cat "${PLUGINS_RESPONSE_FILE}" | node scripts/process-plugins.js
    rm -f "${PLUGINS_RESPONSE_FILE}"
else
    echo "Error fetching plugins list"
    exit 1
fi

# Download and process themes
echo "Fetching themes list..."
THEMES_RESPONSE_FILE=$(make_api_request "/wp-json/techops/v1/themes/list" "themes list")

if [ $? -eq 0 ]; then
    echo "Processing themes data..."
    cat "${THEMES_RESPONSE_FILE}" | node scripts/process-themes.js
    rm -f "${THEMES_RESPONSE_FILE}"
else
    echo "Error fetching themes list"
    exit 1
fi

echo "Content sync completed successfully!" 