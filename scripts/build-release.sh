#!/bin/bash

set -e

# Generate types for all packages
npm run types

# Compile assets
npm run build
npm run build-dev
npm run frontend-build

# Populate resources/dist-package from packages/cms
cp resources/css/ui.css packages/cms/src/ui.css
rsync -a --delete packages/cms/ resources/dist-package/

