<?php

namespace Statamic\View\Blade;

use Illuminate\Support\Str;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\DocumentOptions;

class BladePrecompiler
{
    public static function compile(string $content): string
    {
        if (! Str::contains($content, ['@antlers', '@endantlers'])) {
            return $content;
        }

        return (new AntlersDirectiveTransformer())->transformDocument(
            Document::fromText($content, documentOptions: new DocumentOptions(
                withCoreDirectives: false,
                customDirectives: ['antlers', 'endantlers'],
            ))->resolveStructures()
        );
    }
}
