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

                $format = '%s="%s"';
                if (str_contains($value, '"')) {
                    $format = '%s=\'%s\'';
                }

                return sprintf($format, $attribute, $value);
            })
            ->filter()
            ->implode(' ');
    }

    /**
     * Render HTML attributes from tag params.
     *
     * @param  array  $except  Parameters that should be excluded. Typically used for tag parameters that control behavior.
     * @return string
     */
    protected function renderAttributesFromParams(array $except = [])
    {
        $params = $this->params->reject(fn ($v, $attr) => in_array($attr, $except))->all();

        return $this->renderAttributes($params);
    }

    /**
     * Render HTML attributes and merge attributes from tag params.
     *
     * @return string
     */
    protected function renderAttributesFromParamsWith(array $attrs, array $except = [])
    {
        return collect([
            $this->renderAttributes($attrs),
            $this->renderAttributesFromParams($except),
        ])->filter()->implode(' ');
    }
}
