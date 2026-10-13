<?php

namespace Tests\View\Instrumentation\Concerns;

use ReflectionProperty;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Instrumentation\HtmlInstrumentation;

trait InteractsWithRuntimeConfigurations
{
    protected function runtimeConfiguration(?RuntimeParser $parser = null): RuntimeConfiguration
    {
        $parser ??= $this->app->make(Parser::class);

        return (new ReflectionProperty(RuntimeParser::class, 'runtimeConfiguration'))->getValue($parser);
    }

    protected function instrumentedParser(array $data = [], ?HtmlInstrumentation $instrumenter = null): RuntimeParser
    {
        $parser = $this->parser($data, true);
        $parser->setRuntimeConfiguration($this->runtimeConfiguration());

        Runtime::register($instrumenter ?? HtmlInstrumentation::make());

        return $parser;
    }
}
