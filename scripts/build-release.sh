#!/bin/bash

set -e

# Check if version argument is provided
if [ -z "$1" ]; then
    echo "Usage: ./build-release.sh VERSION"
    echo "Example: ./build-release.sh 6.0.0-alpha.14"
    exit 1
fi

VERSION=$1

# Generate types for all packages
npm run types

# Compile assets
npm run build
npm run build-dev
npm run frontend-build

# Create tarballs for the Laravel package
cd resources/dist
tar -czvf ../dist.tar.gz *
cd ../dist-dev
tar -czvf ../dist-dev.tar.gz *
cd ../dist-frontend
tar -czvf ../dist-frontend.tar.gz *
cd ../..

# Create npm pack tarballs for packages with stable symlinks
mkdir -p temp-packages

cd packages/ui
npm version $VERSION --no-git-tag-version
npm pack --pack-destination ../../temp-packages
cd ../../temp-packages
ln -sf statamic-ui-*.tgz ui.tgz
cd ..

cp packages/ui/src/ui.css packages/cms/src/ui.css
cd packages/cms
npm version $VERSION --no-git-tag-version
npm pack --pack-destination ../../temp-packages
cd ../../temp-packages
ln -sf statamic-cms-*.tgz cms.tgz
cd ..

# Create consolidated packages tarball with flat structure
cd temp-packages
tar -czvf ../resources/dist-packages.tar.gz *
cd ..
rm -rf temp-packages

# Reset package versions back to 0.0.0
cd packages/ui
npm version 0.0.0 --no-git-tag-version
cd ../cms
npm version 0.0.0 --no-git-tag-version
cd ../..

