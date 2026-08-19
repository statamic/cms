#!/usr/bin/env bash
set -uo pipefail

excluded=("src/Statamic.php" "src/namespaced_helpers.php" "src/Translator/MethodDiscovery.php")

is_excluded() {
    local file=$1
    for ex in "${excluded[@]}"; do
        [ "$file" = "$ex" ] && return 0
    done
    return 1
}

# Returns 0 if the file contains an unqualified call to the given function.
# Skips method definitions and qualified calls (Foo::fn(), $x->fn(), $fn()).
has_unqualified_call() {
    local file=$1
    local fn=$2

    grep -nE "${fn}[[:space:]]*\(" "$file" 2>/dev/null \
        | grep -vE "(function[[:space:]]+|::|->|\\\$)${fn}[[:space:]]*\(" \
        > /dev/null
}

errors=""

while IFS= read -r file; do
    is_excluded "$file" && continue

    if grep -qE '__[[:space:]]*\([^?]' "$file" && ! grep -qF 'use function Statamic\trans as __;' "$file"; then
        errors+="$file: missing 'use function Statamic\\trans as __;'"$'\n'
    fi

    if has_unqualified_call "$file" "trans" && ! grep -qF 'use function Statamic\trans;' "$file"; then
        errors+="$file: missing 'use function Statamic\\trans;'"$'\n'
    fi

    if has_unqualified_call "$file" "trans_choice" && ! grep -qF 'use function Statamic\trans_choice;' "$file"; then
        errors+="$file: missing 'use function Statamic\\trans_choice;'"$'\n'
    fi
done < <(git ls-files 'src/**/*.php' 'resources/views/**/*.blade.php')

if [ -n "$errors" ]; then
    echo "Files with missing Statamic\\trans imports (see PR #14610):"
    echo
    printf '%s' "$errors"
    exit 1
fi
