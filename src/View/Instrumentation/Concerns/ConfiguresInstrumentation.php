<?php

namespace Statamic\View\Instrumentation\Concerns;

use Statamic\Support\Str;
use Statamic\View\Instrumentation\ComponentPrefixes;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
trait ConfiguresInstrumentation
{
    /**
     * @param  string  $prefix
     * @return $this
     */
    public function comments($prefix = 'antlers')
    {
        $this->prefix($prefix);
        $this->commentsEnabled = true;

        return $this;
    }

    /**
     * @param  string  $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        if (! is_string($prefix)
            || ! preg_match('/^[A-Za-z0-9_-]+$/D', $prefix)
            || str_contains($prefix, '--')) {
            throw new \InvalidArgumentException('HTML instrumentation comment prefixes may contain letters, numbers, underscores, and single hyphens.');
        }

        $this->commentPrefix = $prefix;
        $this->flushResultCache();

        return $this;
    }

    /** @return $this */
    public function withoutComments()
    {
        $this->commentsEnabled = false;
        $this->flushResultCache();

        return $this;
    }

    /** @return $this */
    public function markersUsing(callable $factory)
    {
        $this->commentFactory = $factory;
        $this->flushResultCache();

        return $this;
    }

    /** @return $this */
    public function filter(callable $filter)
    {
        $this->nodeFilter = $filter;
        $this->flushResultCache();

        return $this;
    }

    /** @return $this */
    public function onSkip(callable $callback)
    {
        $this->skipCallback = $callback;
        $this->flushResultCache();

        return $this;
    }

    /**
     * @param  array<string>  $patterns
     * @return $this
     */
    public function matching(array $patterns)
    {
        return $this->filter(static function ($metadata) use ($patterns) {
            foreach ($patterns as $pattern) {
                if (is_string($pattern) && Str::is($pattern, $metadata['expression'])) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @param  string  $attributeName
     * @return $this
     */
    public function attributes($attributeName = 'data-antlers')
    {
        return $this->attribute($attributeName);
    }

    /**
     * @param  string  $attributeName
     * @return $this
     */
    public function attribute($attributeName, ?callable $valueFactory = null)
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

        $this->attributeLayers[$attributeName] = $valueFactory;
        $this->flushResultCache();

        return $this;
    }

    protected function invalidInstrumentationAttributeName($attributeName)
    {
        return ! HtmlSpec::isValidName($attributeName);
    }

    /**
     * @param  string  $attributeName
     * @return $this
     */
    public function jsonAttribute($attributeName = 'data-antlers')
    {
        return $this->attribute($attributeName, static function ($metadata) {
            $json = json_encode($metadata, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);

            if ($json === false) {
                return '[]';
            }

            return $json;
        });
    }

    /**
     * @param  array<string>  $prefixes
     * @return $this
     */
    public function componentPrefixes(array $prefixes)
    {
        $this->componentPrefixes = ComponentPrefixes::merge($prefixes);
        $this->flushResultCache();

        return $this;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return $this
     */
    public function withMetadata(array $metadata)
    {
        $this->extraMetadata = array_merge($this->extraMetadata, $metadata);
        $this->flushResultCache();

        return $this;
    }
}
