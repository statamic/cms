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
    private const RESOLVABLE = '/(?<!\\\\)((?:\\\\\\\\)*+)(?:\[\[\s*('.self::VALUE.')\s*\]\]|(\\\\)\[\[|\[(?=\[))/';

    public static function isValidValue(string $value): bool
    {
        return (bool) preg_match('/^'.self::VALUE.'$/D', $value);
    }

    // Pass `$using` to run a template pass (e.g. Antlers) over the content. Neither
    // order is safe without it: parsing first lets submitted values be read as
    // mentions or have their backslashes eaten, and resolving first turns them
    // into template source. So mentions become inert per-call placeholders, the
    // callback gets the template, and the values go in afterwards. The callback
    // never sees a value.
    public static function replace(string $content, array $values, ?callable $using = null): string
    {
        if ($using === null) {
            return self::resolve($content, $values);
        }

        do {
            $nonce = bin2hex(random_bytes(8));
        } while (str_contains($content, $nonce));

        $placeholders = [];
        $i = 0;

        foreach ($values as $handle => $value) {
            $placeholders['mention'.$nonce.'n'.$i++.'e'] = $value;
        }

        $template = self::resolve($content, array_combine(array_map('strval', array_keys($values)), array_keys($placeholders)));

        return strtr((string) $using($template), $placeholders);
    }

    private static function resolve(string $content, array $values): string
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
