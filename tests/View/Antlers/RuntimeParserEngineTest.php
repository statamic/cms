<?php

namespace Tests\View\Antlers;

use Tests\TestCase;

class RuntimeParserEngineTest extends TestCase
{
    use EngineTests;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->config->set('statamic.antlers.version', 'runtime');
    }
}
