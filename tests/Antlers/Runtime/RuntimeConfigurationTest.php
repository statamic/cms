<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Tests\Antlers\ParserTestCase;

class RuntimeConfigurationTest extends ParserTestCase
{
    public function test_unpaired_loops_will_throw_fatal_error_when_configured()
    {
        $config = new RuntimeConfiguration();
        $config->fatalErrorOnUnpairedLoop = true;

        $vars = ['test' => ['one', 'two', 'three']];

        $this->expectException(AntlersException::class);
        $this->renderStringWithConfiguration('{{ test }}', $config, $vars);
    }
}
