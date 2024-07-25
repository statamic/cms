<?php

namespace Statamic\Dictionaries;

use Statamic\Extend\HasFields;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\DictionaryType;

abstract class Dictionary
{
    use HasFields, HasHandle, HasTitle, RegistersItself;

    protected static $binding = 'dictionaries';

    protected $fields = [];
    protected $context = [];

    /**
     * Returns a key/value array of options.
     */
    abstract public function options(?string $search = null): array;

    /**
     * Returns a single option.
     */
    abstract public function get(string $key): ?Item;

    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    protected function fieldItems()
    {
        return $this->fields;
    }

    public function getGqlType()
    {
        $name = str(class_basename($this))->singular()->value();

        return new DictionaryType($name, $this->getGqlFields());
    }

    protected function getGqlFields(): array
    {
        // By default, we will make non-nullable strings out of all the keys
        // of the first option. This is an easy way for it to "just work",
        // and of course it may be easily overridden per dictionary.
        $firstOption = collect($this->options())->keys()->first();

        return collect($this->get($firstOption))
            ->map(fn ($value) => ['type' => GraphQL::nonNull($this->getInferredGqlType($value))])
            ->all();
    }

    private function getInferredGqlType($value)
    {
        if (is_int($value)) {
            return GraphQL::int();
        }

        if (is_bool($value)) {
            return GraphQL::boolean();
        }

        return GraphQL::string();
    }
}
