<?php

namespace Statamic\Fieldtypes\AutocompleteEditor;

class Mentions
{
    public const VALUE = '[\w.-]+';

    // Mirrors MENTION_TOKEN in resources/js/components/ui/AutocompleteEditor/mentionToken.js.
    public const TOKEN = '/\[\[\s*('.self::VALUE.')\s*\]\]/';

    // Mirrors the decoder in resources/js/components/ui/AutocompleteEditor/text.js.
    //
    // Plain text values escape a literal `[[` as `\[[` and double any backslashes
    // directly before a `[[`, so the parity of that run decides: odd is an escaped
    // literal, even is halved and followed by a real token. A `[[` that doesn't
    // start a token yields a single `[` so a mention right after it still matches.
    //
    // Markdown values never reach this with a backslash touching `[[`. Augmenting
    // encodes literal brackets and any backslashes before a token as entities, so
    // only real tokens remain and they resolve exactly as before.
    private const RESOLVABLE = '/(?<!\\\\)((?:\\\\\\\\)*)(?:\[\[\s*('.self::VALUE.')\s*\]\]|(\\\\)\[\[|\[(?=\[))/';

    public static function isValidValue(string $value): bool
    {
        return (bool) preg_match('/^'.self::VALUE.'$/D', $value);
    }

    public static function replace(string $content, array $values): string
    {
        return preg_replace_callback(self::RESOLVABLE, function ($matches) use ($values) {
            $backslashes = str_repeat('\\', strlen($matches[1]) / 2);

            if ($matches[2] !== null) {
                // Values are substituted verbatim. Escaping them for whatever context
                // the result ends up in is deliberately the consumer's job.
                return $backslashes.($values[$matches[2]] ?? '');
            }

            return $backslashes.($matches[3] !== null ? '[[' : '[');
        }, $content, flags: PREG_UNMATCHED_AS_NULL);
    }
}
