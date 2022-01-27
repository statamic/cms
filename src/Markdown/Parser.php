<?php

namespace Statamic\Markdown;

use Closure;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\MarkdownConverter;
use League\Config\ReadOnlyConfiguration;
use Statamic\Support\Arr;

class Parser
{
    protected $converter;
    protected $extensions = [];
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function parse(string $markdown): string
    {
        return $this->converter()->convertToHtml($markdown);
    }

    public function converter(): MarkdownConverter
    {
        if ($this->converter) {
            return $this->converter;
        }

        $env = new Environment($this->config);

        $env->addExtension(new CommonMarkCoreExtension);

        foreach ($this->extensions() as $ext) {
            $env->addExtension($ext);
        }

        return $this->converter = new MarkdownConverter($env);
    }

    public function environment(): Environment
    {
        return $this->converter()->getEnvironment();
    }

    public function addExtension(Closure $closure): self
    {
        $this->converter = null;

        $this->extensions[] = $closure;

        return $this;
    }

    public function addExtensions(Closure $closure): self
    {
        return $this->addExtension($closure);
    }

    public function extensions(): array
    {
        $exts = [];

        foreach ($this->extensions as $closure) {
            foreach (Arr::wrap($closure()) as $ext) {
                $exts[] = $ext;
            }
        }

        return $exts;
    }

    public function withStatamicDefaults()
    {
        return $this->newInstance()->addExtensions(function () {
            return [
                new \League\CommonMark\Extension\Table\TableExtension,
                new \League\CommonMark\Extension\Attributes\AttributesExtension,
                new \League\CommonMark\Extension\Strikethrough\StrikethroughExtension,
            ];
        });
    }

    public function withAutoLinks(): self
    {
        return $this->newInstance()->addExtension(function () {
            return new AutolinkExtension;
        });
    }

    public function withAutoLineBreaks(): self
    {
        return $this->newInstance([
            'renderer' => [
                'soft_break' => "<br />\n",
            ],
        ]);
    }

    public function withMarkupEscaping(): self
    {
        return $this->newInstance(['html_input' => 'escape']);
    }

    public function withSmartPunctuation(): self
    {
        return $this->newInstance()->addExtension(function () {
            return new SmartPunctExtension;
        });
    }

    public function config(): ReadOnlyConfiguration
    {
        return $this->environment()->getConfiguration();
    }

    public function newInstance(array $config = [])
    {
        $parser = new self(array_replace_recursive($this->config(), $config));

        foreach ($this->extensions as $ext) {
            $parser->addExtensions($ext);
        }

        return $parser;
    }
}
