<?php

namespace Statamic\Fieldtypes\AutocompleteEditor;

class Mentions
{
    public const VALUE = '[\w.-]+';

    // Mirrors the tokenizer in resources/js/components/ui/AutocompleteEditor/mentionToken.js.
    //
    // Markdown escaping puts a backslash *between* the brackets, so an author's
    // literal is stored as `\[\[ foo \]\]` and contains no `[[` or `]]` pair at
    // all. That means escaped literals are skipped without needing a lookbehind,
    // and `\\[[ foo ]]` (an escaped backslash followed by a real token) still
    // resolves, which a lookbehind would have wrongly rejected.
    public const TOKEN = '/\[\[\s*('.self::VALUE.')\s*\]\]/';

    public static function isValidValue(string $value): bool
    {
        return (bool) preg_match('/^'.self::VALUE.'$/D', $value);
    }

    public static function replace(string $content, array $values): string
    {
        return preg_replace_callback(
            self::TOKEN,
            // Values are substituted verbatim. Escaping them for whatever context
            // the result ends up in is deliberately the consumer's job.
            fn ($matches) => $values[$matches[1]] ?? '',
            $content
        );
    }
}
