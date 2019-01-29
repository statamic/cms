<?php

namespace Statamic\Actions;

use Statamic\API\Str;
use Illuminate\Contracts\Support\Arrayable;

abstract class Action implements Arrayable
{
    protected $confirm = true;
    protected $dangerous = false;

    public static function title()
    {
        return static::$title ?? Str::humanize(static::handle());
    }

    public static function handle()
    {
        return static::$handle ?? snake_case(str_replace((new \ReflectionClass(static::class))->getNamespaceName().'\\', '', static::class));
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'confirm' => $this->confirm,
            'dangerous' => $this->dangerous
        ];
    }
}
