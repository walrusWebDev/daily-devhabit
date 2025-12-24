#!/bin/bash

# Configuration
PLUGIN_SLUG="daily-devhabit"
VERSION="1.1.0"
ZIP_NAME="${PLUGIN_SLUG}-v${VERSION}.zip"
BUILD_DIR="./build_tmp"

echo "🚀 Starting build for ${ZIP_NAME}..."

# 1. Clean up old builds
rm -rf "$BUILD_DIR"
rm -f "$ZIP_NAME"
mkdir -p "$BUILD_DIR/$PLUGIN_SLUG"

echo "📂 Copying files to clean directory..."

# 2. Copy files (Add/Remove files from this list as needed)
# We use rsync to explicitly include only what we want, preserving permissions
rsync -av --progress . "$BUILD_DIR/$PLUGIN_SLUG" \
    --exclude '.git' \
    --exclude '.gitignore' \
    --exclude '.DS_Store' \
    --exclude 'build_release.sh' \
    --exclude 'node_modules' \
    --exclude 'tests' \
    --exclude 'README.md' \
    --exclude '*.zip'

# Note: If you have a 'vendor' folder (Composer), make sure it IS included but without dev dependencies.
# If you are using Composer, uncomment the lines below:
# cd "$BUILD_DIR/$PLUGIN_SLUG"
# composer install --no-dev --optimize-autoloader
# cd ../..

echo "📦 Zipping..."

# 3. Create the Zip archive
cd "$BUILD_DIR"
zip -r "../$ZIP_NAME" "$PLUGIN_SLUG"
cd ..

# 4. Cleanup
rm -rf "$BUILD_DIR"

echo "✅ Build Complete!"
echo "📍 File saved to: $(pwd)/$ZIP_NAME"