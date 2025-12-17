<?php

namespace Statamic\View\Scaffolding;

use Statamic\Facades\File;
use Stringable;

class ScaffoldedTemplate implements Stringable
{
    private ?string $savedPath = null;

    public function __construct(
        private string $content,
        private TemplateGenerator $generator
    ) {
    }

    public function content(): string
    {
        return $this->content;
    }

    public function save(string $path): self
    {
        $fullPath = resource_path("views/{$path}{$this->generator->extension()}");

        $directory = dirname($fullPath);

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (! File::exists($fullPath)) {
            File::put($fullPath, $this->content);
            $this->savedPath = $fullPath;
        }

        return $this;
    }

    public function path(): ?string
    {
        return $this->savedPath;
    }

    public function generator(): TemplateGenerator
    {
        return $this->generator;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
