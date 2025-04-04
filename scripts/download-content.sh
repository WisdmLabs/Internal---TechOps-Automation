#!/bin/bash

# Exit on any error
set -e

# Configuration
BASE_DIR="wp-content"
PLUGINS_DIR="$BASE_DIR/plugins"
THEMES_DIR="$BASE_DIR/themes"

# Function to check if curl request was successful
check_curl_response() {
    if [ $1 -ne 0 ]; then
        echo "Error: API request failed with status code $1"
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

AUTH_HEADER="Authorization: Basic $WP_AUTH_TOKEN"

echo "Starting WordPress content sync..."
echo "Using site URL: $SITE_URL"

# Download plugins list
echo "Fetching plugins list..."
PLUGINS_RESPONSE=$(curl -s -w "%{http_code}" -H "$AUTH_HEADER" "$SITE_URL/wp-json/techops/v1/plugins/list")
HTTP_STATUS=${PLUGINS_RESPONSE: -3}
PLUGINS_DATA=${PLUGINS_RESPONSE:0:${#PLUGINS_RESPONSE}-3}

check_curl_response $HTTP_STATUS

echo "$PLUGINS_DATA" | node scripts/process-plugins.js

if [ $? -ne 0 ]; then
    echo "Error processing plugins"
    exit 1
fi

# Download themes list
echo "Fetching themes list..."
THEMES_RESPONSE=$(curl -s -w "%{http_code}" -H "$AUTH_HEADER" "$SITE_URL/wp-json/techops/v1/themes/list")
HTTP_STATUS=${THEMES_RESPONSE: -3}
THEMES_DATA=${THEMES_RESPONSE:0:${#THEMES_RESPONSE}-3}

check_curl_response $HTTP_STATUS

echo "$THEMES_DATA" | node scripts/process-themes.js

if [ $? -ne 0 ]; then
    echo "Error processing themes"
    exit 1
fi

echo "Content sync completed successfully!" 