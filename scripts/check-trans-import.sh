#!/usr/bin/env bash
set -euo pipefail

missing=$(
    while IFS= read -r file; do
        if [ "$file" = "src/Statamic.php" ]; then
            continue
        fi

        if grep -qE '__[[:space:]]*\([^?]' "$file" && ! grep -qF 'use function Statamic\trans as __;' "$file"; then
            echo "$file"
        fi
    done < <(git ls-files 'src/**/*.php' 'resources/views/**/*.blade.php')
)

if [ -n "$missing" ]; then
    echo "The following files use __() without 'use function Statamic\\trans as __;':"
    echo "$missing"
    echo
    echo "Add the import to fix the lang-file/title collision (see issue #14609)."
    exit 1
fi
