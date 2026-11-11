<?php

namespace Statamic\Fields;

use Illuminate\Support\Facades\Cache;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

/**
 * Parsed blueprint and fieldset YAML, kept in the cache
 * between requests. Each file's entry is stored with a hash of the
 * contents it was parsed from, so a changed file is parsed again.
 *
 * @internal
 */
class ParsedYamlFiles
{
    public static function parse(string $path)
    {
        $contents = File::get($path);

        // Keyed on the path within the app, so a deploy into a new release directory reuses the entries.
        $key = 'statamic.parsed-yaml::'.md5(Str::after($path, base_path()));
        $hash = hash('xxh128', (string) $contents);
        $cache = Cache::store();

        $cached = $cache->get($key);

        if (is_array($cached) && ($cached['hash'] ?? null) === $hash) {
            return $cached['data'];
        }

        $data = YAML::file($path)->parse($contents);

        if (is_array($data)) {
            $cache->forever($key, ['hash' => $hash, 'data' => $data]);
        }

        return $data;
    }
}
