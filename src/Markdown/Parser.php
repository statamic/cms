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

        foreach ($this->extensions as $closure) {
            foreach (Arr::wrap($closure()) as $ext) {
                $env->addExtension($ext);
            }
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

    public function withAutoLinks(): Parser
    {
        $parser = new static;

        return $parser->addExtension(function () {
            return new AutolinkExtension;
        });
    }

    public function withAutoLineBreaks(): Parser
    {
        return new self(array_replace_recursive($this->environment()->getConfig(), [
            'renderer' => [
                'soft_break' => "<br />\n",
            ]
        ]));
    }

    public function withMarkupEscaping(): Parser
    {
        return new self(array_replace_recursive($this->environment()->getConfig(), [
            'html_input' => 'escape'
        ]));
    }

    public function withSmartPunctuation(): Parser
    {
        $parser = new static;

        return $parser->addExtension(function () {
            return new SmartPunctExtension;
        });
    }
}
