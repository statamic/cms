#!/bin/bash

# Script to build release archives and extract them to their corresponding directories to simulate a real release structure during development.

set -e  # Exit on error

# Check if version argument is provided
if [ -z "$1" ]; then
    echo "Usage: ./test-release.sh VERSION"
    echo "Example: ./test-release.sh 6.0.1"
    exit 1
fi

VERSION=$1

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RESOURCES_DIR="resources"

# Array of archive names (without .tar.gz extension) to extract to resources
ARCHIVES=(
    "dist"
    "dist-dev"
    "dist-frontend"
)

bash "${SCRIPT_DIR}/build-release.sh" "$VERSION"

for archive in "${ARCHIVES[@]}"; do
    archive_file="${RESOURCES_DIR}/${archive}.tar.gz"
    target_dir="${RESOURCES_DIR}/${archive}"

    if [ -f "$archive_file" ]; then
        rm -rf "$target_dir"
        mkdir -p "$target_dir"
        tar -xzf "$archive_file" -C "$target_dir"
    else
        echo "⚠ Skipping ${archive_file} (file not found) - archive"
    fi
done

# Extract packages.tar.gz to packages/
packages_file="${RESOURCES_DIR}/dist-packages.tar.gz"
if [ -f "$packages_file" ]; then
    echo "Extracting ${packages_file} to packages/..."
    tar -xzf "$packages_file" -C packages
else
    echo "⚠ Skipping ${packages_file} (file not found) - packages"
fi

echo "Done."
