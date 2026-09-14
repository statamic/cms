<?php

namespace Statamic\Forms\Insights;

use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class Insight
{
    use HasHandle, HasTitle, RegistersItself;

    protected ?string $component = null;

    public function component(): string
    {
        return $this->component ?? str_replace('_', '-', static::handle()).'-insight';
    }

    abstract public function props(Collection $values): array;
}
