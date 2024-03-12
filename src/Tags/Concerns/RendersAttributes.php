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
     * Render HTML attributes from tag params.
     *
     * Parameters that are not prefixed with attr: will be automatically removed.
     *
     * @return string
     */
    protected function renderAttributesFromParams()
    {
        $params = $this->params->filter(function ($value, $attribute) {
            return preg_match('/^attr:/', $attribute);
        })->all();

        return $this->renderAttributes($params);
    }

    /**
     * Render HTML attributes and merge attributes from tag params.
     *
     * @return string
     */
    protected function renderAttributesFromParamsWith(array $attrs)
    {
        return collect([
            $this->renderAttributes($attrs),
            $this->renderAttributesFromParams(),
        ])->filter()->implode(' ');
    }
}
