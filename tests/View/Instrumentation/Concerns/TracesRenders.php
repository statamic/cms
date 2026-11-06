<?php

namespace Tests\View\Instrumentation\Concerns;

use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Runtime\Tracing\TraceManager;

trait TracesRenders
{
    protected function tracingParser(...$tracers): RuntimeParser
    {
        $manager = new TraceManager;

        foreach ($tracers as $tracer) {
            $manager->registerTracer($tracer);
        }

        $configuration = new RuntimeConfiguration;
        $configuration->isTracingEnabled = true;
        $configuration->traceManager = $manager;

        $parser = $this->parser([], true);
        $parser->setRuntimeConfiguration($configuration);

        return $parser;
    }
}
