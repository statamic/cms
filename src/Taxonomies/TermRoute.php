<?php

namespace Statamic\Taxonomies;

use Statamic\Support\Str;
use Symfony\Component\Routing\Route;

class TermRoute
{
    /**
     * Matches a `{token}` placeholder, tolerating the surrounding whitespace that
     * `UrlBuilder` allows. The character class matches Symfony's own tokenizer.
     */
    private const TOKEN = '#\{\s*([\w\x80-\xFF]+)\s*\}#';

    /**
     * Collapse `{ slug }` to `{slug}` so that url generation and url matching,
     * which tokenize differently, always agree on what a placeholder is.
     */
    public static function canonicalize(string $pattern): string
    {
        return preg_replace(self::TOKEN, '{$1}', $pattern) ?? $pattern;
    }

    /**
     * Unlike collection routes, term routes have to be matched in reverse, which
     * means the pattern itself gets inverted into a regex. Antlers can't be
     * inverted, so it's not supported here.
     */
    public static function containsAntlers(string $pattern): bool
    {
        return Str::contains($pattern, '{{');
    }

    /**
     * @throws \DomainException|\LogicException when the pattern can't be compiled.
     */
    public static function toRegex(string $pattern): string
    {
        return (new Route($pattern, [], ['parent_uri' => '.+'], ['utf8' => true]))
            ->compile()
            ->getRegex();
    }

    /**
     * The pattern a term at the root of the tree matches, since Symfony can only
     * make a trailing placeholder optional, never one in the middle.
     */
    public static function withoutParentUri(string $pattern): string
    {
        return preg_replace('#/?\{parent_uri\}#', '', $pattern) ?: '/';
    }
}
