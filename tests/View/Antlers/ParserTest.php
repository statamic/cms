<?php

namespace Tests\View\Antlers;

use Statamic\Facades\Antlers;
use Tests\TestCase;

class ParserTest extends TestCase
{
    use ParserTests;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);
        $app['config']->set('statamic.antlers.version', 'regex');
    }

    private function renderString($template, $data = [])
    {
        return (string) $this->parser()->parse($template, $data);
    }

    private function parser()
    {
        return Antlers::parser();
    }
}
