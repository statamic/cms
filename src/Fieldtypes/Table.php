<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\GraphQL\Types\TableRowType;

class Table extends Fieldtype
{
    protected $categories = ['structured'];

    protected function configFieldItems(): array
    {
        return [
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'table',
            ],
            'max_rows' => [
                'display' => __('Max Columns'),
                'instructions' => __('statamic::fieldtypes.table.config.max_rows'),
                'type' => 'integer',
            ],
            'max_columns' => [
                'display' => __('Max Columns'),
                'instructions' => __('statamic::fieldtypes.table.config.max_columns'),
                'type' => 'integer',
            ],
        ];
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::type(TableRowType::NAME));
    }
}
