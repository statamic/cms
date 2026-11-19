<?php

namespace Statamic\CP\Breadcrumbs;

use Illuminate\Support\Collection;

class Breadcrumb
{
    public function __construct(
        protected string $text,
        protected ?string $url = null,
        protected ?string $icon = null,
        protected ?array $links = null,
        protected ?string $createLabel = null,
        protected ?string $createUrl = null,
        protected ?string $configureUrl = null
    ) {
    }

    public function text(): string
    {
        return $this->text;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function icon(): ?string
    {
        return $this->icon;
    }

    public function links(): Collection
    {
        return collect($this->links)->map(fn (array $link) => (object) $link);
    }

    public function hasLinks(): bool
    {
        return $this->links()->isNotEmpty();
    }

    public function createLabel(): ?string
    {
        return $this->createLabel;
    }

    public function createUrl(): ?string
    {
        return $this->createUrl;
    }

    public function configureUrl(): ?string
    {
        return $this->configureUrl;
    }

    public function hasConfigureUrl(): bool
    {
        return ! is_null($this->configureUrl);
    }
}
