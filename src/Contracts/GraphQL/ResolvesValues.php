<?php

namespace Statamic\Contracts\GraphQL;

interface ResolvesValues
{
    /**
     * Get the value to be used in a GraphQL field.
     *
     * @param  string  $field  The name of the field.
     * @return mixed
     */
    public function resolveGqlValue($field);

    /**
     * Get the value to be used in a GraphQL field, without augmentation if applicable.
     *
     * @param  string  $field  The name of the field.
     * @return mixed
     */
    public function resolveRawGqlValue($field);
}
