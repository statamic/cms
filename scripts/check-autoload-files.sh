#!/usr/bin/env bash
set -uo pipefail

EXPECTED='["src/helpers.php","src/namespaced_helpers.php","src/View/Blade/helpers.php"]'
ACTUAL=$(jq -c '.autoload.files' composer.json)

if [ "$ACTUAL" != "$EXPECTED" ]; then
    echo "composer.json autoload.files has changed and must be reviewed."
    echo "Expected: $EXPECTED"
    echo "Actual:   $ACTUAL"
    exit 1
fi

echo "autoload.files matches the approved allowlist."
