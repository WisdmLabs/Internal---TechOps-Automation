#!/bin/bash

# Configuration
BASE_DIR="wp-content"
PLUGINS_DIR="$BASE_DIR/plugins"
THEMES_DIR="$BASE_DIR/themes"

# Ensure directories exist
mkdir -p "$PLUGINS_DIR"
mkdir -p "$THEMES_DIR"

# Authentication
AUTH_HEADER="Authorization: Basic $WP_AUTH_TOKEN"

echo "Starting WordPress content sync..."

# Download plugins list
echo "Fetching plugins list..."
curl -H "$AUTH_HEADER" "$SITE_URL/wp-json/techops/v1/plugins/list" | node scripts/process-plugins.js

if [ $? -ne 0 ]; then
    echo "Error processing plugins"
    exit 1
fi

# Download themes list
echo "Fetching themes list..."
curl -H "$AUTH_HEADER" "$SITE_URL/wp-json/techops/v1/themes/list" | node scripts/process-themes.js

if [ $? -ne 0 ]; then
    echo "Error processing themes"
    exit 1
fi

echo "Content sync completed successfully!" 