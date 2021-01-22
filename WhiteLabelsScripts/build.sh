#!/bin/bash

CURRENT_DIR=$(pwd)
TMPDIR="/tmp"
BRANDS=(barclays postfinance kbc concardis viveum payglobe santander)
SOURCE_DIR="$TMPDIR/ingenico_epayments"
BUILD_DIR="$CURRENT_DIR/build"

echo "Source dir: $SOURCE_DIR"
echo "Build dir: $BUILD_DIR"

# Prepare temporary source dir
rm -rf "$SOURCE_DIR" > /dev/null
mkdir $SOURCE_DIR > /dev/null
rsync -av --progress "../" "$SOURCE_DIR" --exclude "$CURRENT_DIR" > /dev/null
cd "$SOURCE_DIR" > /dev/null

# Remove unnecessary files

#rm -rf "$SOURCE_DIR/WhiteLabelsScripts"
rm -rf "$SOURCE_DIR/.git" > /dev/null
rm -rf "$SOURCE_DIR/vendor/ingenico/ogone-sdk-php/.git" > /dev/null
rm -rf "$SOURCE_DIR/vendor/ingenico/ogone-client/.git" > /dev/null

# Install composer dependencies
# TODO: repositories should be public
#composer require

# Install gulp dependencies
npm install

# Prepare directory with build packages
#mkdir $CURRENT_DIR/build > /dev/null

# Start building
for brand in ${BRANDS[*]}
do
    echo "Building $brand..."
    "$CURRENT_DIR/mkbrand.sh" $brand "$SOURCE_DIR" "$BUILD_DIR" 2>&1 > "$TMPDIR/$brand.log"
    echo "done"
done

# Remove temporary files
rm -rf "$SOURCE_DIR" > /dev/null

echo "Finished. Packages are placed in $BUILD_DIR"
