<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Facade\Ignition\Exceptions\ViewException;
use Tests\Antlers\ParserTestCase;

class RuntimeConfigurationTest extends ParserTestCase
{
    public function test_unpaired_loops_will_throw_fatal_error_when_configured()
    {
        $config = new RuntimeConfiguration();
        $config->fatalErrorOnUnpairedLoop = true;

        $vars = ['test' => ['one', 'two', 'three']];

        $this->expectException(ViewException::class);
        $this->renderStringWithConfiguration('{{ test }}', $config, $vars);
    }
}
