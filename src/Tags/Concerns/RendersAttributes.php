<?php

namespace Statamic\Tags\Concerns;

use Statamic\Support\Str;

trait RendersAttributes
{
    /**
     * Render HTML attributes.
     *
     * @param  array  $attributes
     * @return string
     */
    protected function renderAttributes($attributes)
    {
        return collect($attributes)
            ->map(function ($value, $attribute) {
                if (Str::startsWith($attribute, 'attr:')) {
                    $attribute = mb_substr($attribute, 5);
                }

                if ($value === null) {
                    return;
                }

                $value = is_bool($value)
                    ? ($value ? 'true' : 'false')
                    : $value;

                return sprintf('%s="%s"', $attribute, $value);
            })
            ->filter()
            ->implode(' ');
    }

    /**
     * Render HTML attributes from rest of tag params.
     *
     * Parameters that are not prefixed with attr: will be automatically removed.
     *
     * @param  array  $additional  A set of additional values that will be merged with the rendered attributes.
     * @return string
     */
    protected function renderAttributesFromParams($additional = [])
    {
        // Additional first to preserve existing order behavior.
        $params = collect($additional)->mapWithKeys(function ($value, $attribute) {
            return ['attr:'.$attribute => $value];
        })
            ->merge($this->params->all())
            ->filter(function ($value, $attribute) {
                return preg_match('/^attr:/', $attribute);
            })->all();

        return $this->renderAttributes($params);
    }
}
