<?php

namespace Statamic\View\Instrumentation\Concerns;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\View\Instrumentation\ComponentPrefixes;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
trait ConfiguresInstrumentation
{
    public function comments(string $prefix = 'antlers'): static
    {
        if (! preg_match('/^[A-Za-z0-9_-]+$/D', $prefix)
            || str_contains($prefix, '--')) {
            throw new \InvalidArgumentException('HTML instrumentation comment prefixes may contain letters, numbers, underscores, and single hyphens.');
        }

        $this->commentPrefix = $prefix;
        $this->commentsEnabled = true;
        $this->flushResultCache();

        return $this;
    }

    public function withoutComments(): static
    {
        $this->commentsEnabled = false;
        $this->flushResultCache();

        return $this;
    }

    public function markersUsing(callable $factory): static
    {
        $this->commentFactory = Closure::fromCallable($factory);
        $this->flushResultCache();

        return $this;
    }

    public function filter(callable $filter): static
    {
        $filter = Closure::fromCallable($filter);
        $previous = $this->nodeFilter;

        $this->nodeFilter = $previous === null
            ? $filter
            : static fn ($metadata, $region) => $previous($metadata, $region) && $filter($metadata, $region);

        $this->flushResultCache();

        return $this;
    }

    /** @param  string|array<string>  ...$patterns */
    public function only(string|array ...$patterns): static
    {
        $patterns = $this->expressionPatterns($patterns);

        return $this->filter(static fn ($metadata) => Str::is($patterns, $metadata['expression']));
    }

    /** @param  string|array<string>  ...$patterns */
    public function except(string|array ...$patterns): static
    {
        $patterns = $this->expressionPatterns($patterns);

        return $this->filter(static fn ($metadata) => ! Str::is($patterns, $metadata['expression']));
    }

    /**
     * @param  array<int, string|array<string>>  $patterns
     * @return string[]
     */
    protected function expressionPatterns(array $patterns): array
    {
        return array_values(array_filter(Arr::flatten($patterns), 'is_string'));
    }

    public function onSkip(callable $callback): static
    {
        $this->skipCallback = Closure::fromCallable($callback);
        $this->flushResultCache();

        return $this;
    }

    public function attributes(string ...$attributeNames): static
    {
        foreach ($attributeNames ?: ['data-antlers'] as $attributeName) {
            $this->attribute($attributeName);
        }

        return $this;
    }

    public function attribute(string $attributeName, ?callable $valueFactory = null): static
    {
        if ($this->invalidInstrumentationAttributeName($attributeName)) {
            throw new \InvalidArgumentException('Invalid HTML instrumentation attribute name.');
        }

        if ($valueFactory === null) {
            $valueFactory = static function ($metadata) {
                $json = json_encode($metadata, JSON_INVALID_UTF8_SUBSTITUTE);

                if ($json === false) {
                    $json = '[]';
                }

                return base64_encode($json);
            };
        }

        $this->attributeLayers[$attributeName] = Closure::fromCallable($valueFactory);
        $this->flushResultCache();

        return $this;
    }

    protected function invalidInstrumentationAttributeName(string $attributeName): bool
    {
        return ! HtmlSpec::isValidName($attributeName);
    }

    public function jsonAttribute(string $attributeName = 'data-antlers'): static
    {
        return $this->attribute($attributeName, static function ($metadata) {
            $json = json_encode($metadata, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);

            if ($json === false) {
                return '[]';
            }

            return $json;
        });
    }

    /** @param  string|array<string>  ...$prefixes */
    public function componentPrefixes(string|array ...$prefixes): static
    {
        $this->componentPrefixes = ComponentPrefixes::merge(Arr::flatten($prefixes));
        $this->flushResultCache();

        return $this;
    }

    /** @param  array<string, mixed>  $metadata */
    public function withMetadata(array $metadata): static
    {
        $this->extraMetadata = array_merge($this->extraMetadata, $metadata);
        $this->flushResultCache();

        return $this;
    }
}
