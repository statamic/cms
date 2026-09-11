<?php

namespace Statamic\View\Blade;

use Illuminate\Support\Str;
use Statamic\Facades\File;

class AntlersBladePrecompiler
{
    public static function compile(string $content): string
    {
        if (! Str::contains($content, ['@antlers', '@endantlers'])) {
            return $content;
        }

        $pattern = '/@antlers(.*?)@endantlers/s';

        preg_match_all($pattern, $content, $matches);

        if (! $matches || count($matches) != 2) {
            return $content;
        }

        for ($i = 0; $i < count($matches[0]); $i++) {
            $original = $matches[0][$i];
            $innerContent = $matches[1][$i];

            $contentHash = sha1($innerContent);
            $fileName = 'antlers_'.$contentHash;

            File::put(storage_path('statamic/tmp/nocache/'.$fileName.'.antlers.html'), $innerContent);

            $content = str_replace($original, '@include(\'compiled__views::'.$fileName.'\')', $content);
        }

        return $content;
    }
}
