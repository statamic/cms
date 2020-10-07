<?php

namespace Statamic\Tags\Concerns;

trait RendersAttributes
{
    /**
     * Render HTML attributes.
     *
     * @param array $attributes
     * @return string
     */
    protected function renderAttributes($attributes)
    {
        return collect($attributes)
            ->map(function ($value, $attribute) {
                if ($value === true) {
                    $value = 'true';
                }

                return sprintf('%s="%s"', $attribute, $value);
            })
            ->implode(' ');
    }

    /**
     * Render HTML attributes from rest of tag params, except for specifically known params.
     *
     * @param array $knownTagParams
     * @return string
     */
    protected function renderAttributesFromParams($knownTagParams = [])
    {
        $attributes = collect($this->params->all())
            ->except($knownTagParams)
            ->mapWithKeys(function ($value, $attribute) {
                return [preg_replace('/^attr:/', '', $attribute) => $value];
            })
            ->all();

        return $this->renderAttributes($attributes);
    }
}
