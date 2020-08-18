<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Template as TemplateFilter;

class Template extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'hide_partials' => [
                'display' => __('Hide Partials'),
                'instructions' => __('statamic::fieldtypes.template.config.hide_partials'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
        ];
    }

    public function filter()
    {
        return new TemplateFilter($this);
    }
}
