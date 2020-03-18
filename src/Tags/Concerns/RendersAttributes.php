<?php

namespace Statamic\Tags\Concerns;

use Statamic\Extend\HasParameters;

trait RendersAttributes
{
    use HasParameters;

    /**
     * Render HTML attributes.
     *
     * @param array $knownTagParams
     * @return string
     */
    protected function renderAttributes($knownTagParams = [])
    {
        return collect($this->params->all())
            ->except($knownTagParams)
            ->mapWithKeys(function ($value, $attribute) {
                return [preg_replace('/^attr:/', '', $attribute) => $value];
            })
            ->map(function ($value, $attribute) {
                return $value === true
                    ? $attribute
                    : sprintf('%s="%s"', $attribute, $value);
            })
            ->implode(' ');
    }
}
