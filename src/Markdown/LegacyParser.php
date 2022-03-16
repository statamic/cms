<?php

namespace Statamic\Markdown;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Statamic\Support\Arr;

class LegacyParser extends Parser
{
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

    public function config($key = null)
    {
        $config = $this->environment()->getConfig();

        if (! is_null($key)) {
            return Arr::dot($config)[str_replace('/', '.', $key)];
        }

        return $config;
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
