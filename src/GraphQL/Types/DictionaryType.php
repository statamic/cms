<?php

namespace Statamic\GraphQL\Types;

class DictionaryType extends \Rebing\GraphQL\Support\Type
{
    private array $fields;

    public function __construct(string $name, array $fields)
    {
        $this->attributes['name'] = 'Dictionary_'.$name;
        $this->fields = $fields;
    }

    public function fields(): array
    {
        return $this->fields;
    }
}
