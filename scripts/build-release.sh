#!/bin/bash

set -e

# Generate types for all packages
npm run types

# Compile assets
npm run build
npm run build-dev
npm run frontend-build

# Create tarballs for the Laravel package
cd resources
tar -czvf dist.tar.gz dist
tar -czvf dist-dev.tar.gz dist-dev
tar -czvf dist-frontend.tar.gz dist-frontend
cd ..

# Create a tarball for @statamic/cms
cp packages/ui/src/ui.css packages/cms/src/ui.css
cd packages/cms
tar -czvf ../../resources/dist-package.tar.gz *
cd ../..

# Create a tarball for @statamic/ui
cd packages/ui
tar -czvf ../../resources/dist-ui.tar.gz *
cd ../..

