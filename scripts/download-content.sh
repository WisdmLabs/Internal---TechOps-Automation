#!/bin/bash

# Exit on any error
set -e

# Enable debug mode
set -x

# Configuration
BASE_DIR="wp-content"
PLUGINS_DIR="$BASE_DIR/plugins"
THEMES_DIR="$BASE_DIR/themes"

# Function to check if curl request was successful
check_curl_response() {
    local status=$1
    local response=$2
    local endpoint=$3
    
    echo "Response from $endpoint:"
    echo "$response"
    
    if [ $status -ne 200 ]; then
        echo "Error: API request to $endpoint failed with status code $status"
        echo "Response body: $response"
        exit 1
    fi
}

# Ensure directories exist
echo "Creating directory structure..."
mkdir -p "$PLUGINS_DIR"
mkdir -p "$THEMES_DIR"

# Authentication
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

AUTH_HEADER="Authorization: Basic $WP_AUTH_TOKEN"

echo "Starting WordPress content sync..."
echo "Using site URL: $SITE_URL"

# Download plugins list
echo "Fetching plugins list..."
PLUGINS_RESPONSE=$(curl -s -w "\n%{http_code}" -H "$AUTH_HEADER" "$SITE_URL/wp-json/techops/v1/plugins/list")
HTTP_STATUS=$(echo "$PLUGINS_RESPONSE" | tail -n1)
PLUGINS_DATA=$(echo "$PLUGINS_RESPONSE" | sed '$d')

check_curl_response "$HTTP_STATUS" "$PLUGINS_DATA" "plugins list"

# Validate JSON response
if ! echo "$PLUGINS_DATA" | jq empty > /dev/null 2>&1; then
    echo "Error: Invalid JSON response from plugins endpoint"
    echo "Response: $PLUGINS_DATA"
    exit 1
fi

echo "Processing plugins data..."
echo "$PLUGINS_DATA" | node scripts/process-plugins.js

if [ $? -ne 0 ]; then
    echo "Error processing plugins"
    exit 1
fi

# Download themes list
echo "Fetching themes list..."
THEMES_RESPONSE=$(curl -s -w "\n%{http_code}" -H "$AUTH_HEADER" "$SITE_URL/wp-json/techops/v1/themes/list")
HTTP_STATUS=$(echo "$THEMES_RESPONSE" | tail -n1)
THEMES_DATA=$(echo "$THEMES_RESPONSE" | sed '$d')

check_curl_response "$HTTP_STATUS" "$THEMES_DATA" "themes list"

# Validate JSON response
if ! echo "$THEMES_DATA" | jq empty > /dev/null 2>&1; then
    echo "Error: Invalid JSON response from themes endpoint"
    echo "Response: $THEMES_DATA"
    exit 1
fi

echo "Processing themes data..."
echo "$THEMES_DATA" | node scripts/process-themes.js

if [ $? -ne 0 ]; then
    echo "Error processing themes"
    exit 1
fi

echo "Content sync completed successfully!" 