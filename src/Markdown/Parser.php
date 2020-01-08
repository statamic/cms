<?php

namespace Statamic\Markdown;

use Closure;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Ext\Autolink\AutolinkExtension;
use League\CommonMark\Ext\SmartPunct\SmartPunctExtension;
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

    public function converter(): CommonMarkConverter
    {
        if ($this->converter) {
            return $this->converter;
        }

        $env = Environment::createCommonMarkEnvironment();

        $env->mergeConfig($this->config);

        foreach ($this->extensions() as $ext) {
            $env->addExtension($ext);
        }

        return $this->converter = new CommonMarkConverter([], $env);
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

    public function withAutoLinks(): Parser
    {
        return $this->newInstance()->addExtension(function () {
            return new AutolinkExtension;
        });
    }

    public function withAutoLineBreaks(): Parser
    {
        return $this->newInstance([
            'renderer' => [
                'soft_break' => "<br />\n",
            ]
        ]);
    }

    public function withMarkupEscaping(): Parser
    {
        return $this->newInstance(['html_input' => 'escape']);
    }

    public function withSmartPunctuation(): Parser
    {
        return $this->newInstance()->addExtension(function () {
            return new SmartPunctExtension;
        });
    }

    public function config(): array
    {
        return $this->environment()->getConfig();
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
