<?php

namespace Statamic\Markdown;

use Closure;
use Illuminate\Support\Traits\Macroable;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use Statamic\Support\Arr;

class Parser
{
    use Macroable;

    protected $converter;
    protected $extensions = [];
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function parse(string $markdown): string
    {
        return $this->converter()->convert($markdown);
    }

    public function converter(): CommonMarkConverter
    {
        if ($this->converter) {
            return $this->converter;
        }

        $converter = new CommonMarkConverter($this->config);

        $env = $converter->getEnvironment();

        foreach ($this->extensions() as $ext) {
            $env->addExtension($ext);
        }

        return $this->converter = $converter;
    }

    public function environment()
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
                new \League\CommonMark\Extension\DescriptionList\DescriptionListExtension,
                new \League\CommonMark\Extension\Footnote\FootnoteExtension,
                new \League\CommonMark\Extension\TaskList\TaskListExtension,
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

    public function withTableOfContents(): self
    {
        return $this->newInstance()->withHeadingPermalinks()->addExtension(function () {
            return new \League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
        });
    }

    public function withHeadingPermalinks(): self
    {
        return $this->newInstance()->addExtension(function () {
            return new \League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
        });
    }

    public function config($key = null)
    {
        $config = $this->environment()->getConfiguration();

        if (! is_null($key)) {
            return $config->get($key);
        }

        return $config;
    }

    public function newInstance(array $config = [])
    {
        $parser = new self(array_replace_recursive($this->config, $config));

        foreach ($this->extensions as $ext) {
            $parser->addExtensions($ext);
        }

        return $parser;
    }
}
