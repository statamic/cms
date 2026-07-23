<?php

namespace Statamic\Forms\Connections;

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

    public static function handle()
    {
        return Str::removeRight(static::traitHandle(), '_connection');
    }

    public function title()
    {
        if (static::$title) {
            return static::$title;
        }

        return __(Str::title(Str::humanize(static::handle())));
    }

    public function description()
    {
        return $this->description;
    }

    public function icon()
    {
        if (! $this->icon) {
            return null;
        }

        return Str::startsWith($this->icon, '<svg') ? $this->icon : Statamic::svg('icons/'.$this->icon);
    }

    public function developer()
    {
        return $this->developer;
    }

    public function count(Form $form): ?int
    {
        return null;
    }

    public function finalized(Submission $submission)
    {
        return [];
    }

    abstract public function render(Form $form): VueComponent;

    public function routes($router): void
    {
        //
    }
}
