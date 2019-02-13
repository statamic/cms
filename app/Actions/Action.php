<?php

namespace Statamic\Actions;

use Statamic\API\Str;
use Statamic\Fields\Fields;
use Illuminate\Contracts\Support\Arrayable;

abstract class Action implements Arrayable
{
    protected $confirm = true;
    protected $dangerous = false;
    protected $fields = [];
    protected $context = [];

    public static function title()
    {
        return static::$title ?? Str::humanize(static::handle());
    }

    public static function handle()
    {
        return static::$handle ?? snake_case(str_replace((new \ReflectionClass(static::class))->getNamespaceName().'\\', '', static::class));
    }

    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    public function fields()
    {
        $fields = collect($this->fieldItems())->map(function ($field, $handle) {
            return compact('handle', 'field');
        });

        return new Fields($fields);
    }

    public function fieldItems()
    {
        return $this->fields;
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'confirm' => $this->confirm,
            'dangerous' => $this->dangerous,
            'fields' => $this->fields()->toPublishArray(),
            'meta' => $this->fields()->meta(),
            'context' => $this->context
        ];
    }
}
