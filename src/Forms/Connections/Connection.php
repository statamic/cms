<?php

namespace Statamic\Forms\Connections;

use Illuminate\Routing\Router;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;
use Statamic\Statamic;
use Statamic\Support\Str;
use Statamic\Support\VueComponent;

use function Statamic\trans as __;

abstract class Connection
{
    use HasHandle, RegistersItself {
        handle as protected traitHandle;
    }

    protected static $title;
    protected $description;
    protected $icon;
    protected $developer;

    public static function handle(): string
    {
        return Str::removeRight(static::traitHandle(), '_connection');
    }

    public function title(): string
    {
        if (static::$title) {
            return static::$title;
        }

        return __(Str::title(Str::humanize(static::handle())));
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function icon(): ?string
    {
        if (! $this->icon) {
            return null;
        }

        return Str::startsWith($this->icon, '<svg') ? $this->icon : Statamic::svg('icons/'.$this->icon);
    }

    public function developer(): ?string
    {
        return $this->developer;
    }

    public function count(Form $form): ?int
    {
        return null;
    }

    public function finalized(Submission $submission): object|array
    {
        return [];
    }

    abstract public function render(Form $form): VueComponent;

    public function routes(Router $router): void
    {
        //
    }
}
