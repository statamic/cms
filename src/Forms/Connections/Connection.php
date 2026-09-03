<?php

namespace Statamic\Forms\Connections;

use Illuminate\Routing\Router;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;
use Statamic\Statamic;
use Statamic\Support\Str;
use Statamic\Support\VueComponent;

abstract class Connection
{
    use HasHandle, HasTitle, RegistersItself {
        handle as protected traitHandle;
    }

    protected $description;
    protected $icon;
    protected $developer;
    protected $config = [];

    public static function handle(): string
    {
        return Str::removeRight(static::traitHandle(), '_connection');
    }

    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function config(): array
    {
        return $this->config;
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

    public function breadcrumbIcon(): ?string
    {
        return $this->icon();
    }

    public function developer(): ?string
    {
        return $this->developer;
    }

    public function count(Form $form): ?int
    {
        return null;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function finalized(Submission $submission): object|array
    {
        return [];
    }

    abstract public function render(Form $form): VueComponent;

    public function preProcess(array $config, Form $form): array
    {
        return $config;
    }

    public function rules(Form $form): array
    {
        return [];
    }

    public function process(array $data, Form $form): array
    {
        return $data;
    }

    public function routes(Router $router): void
    {
        //
    }
}
