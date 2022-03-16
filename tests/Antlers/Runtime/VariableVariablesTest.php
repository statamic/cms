<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class VariableVariablesTest extends ParserTestCase
{
    public function test_variable_variables_resolves_dynamic_paths()
    {
        // "Variable Variables" first get the value of the
        // initial variable reference, and then parse
        // it into it's own path. The value at the
        // newly parsed path is then returned.

        // Step 1: Get value of "var"
        // Step 2: Parse "title" into its own path variable.
        // Step 3: Return the value of "title"

        $data = [
            'title' => 'Hello, world!',
            'var' => 'title',
        ];

        $this->assertSame('Hello, world!', $this->renderString('{{ @var }}', $data));
    }

    public function test_variable_variables_works_with_strict_variables()
    {
        $data = [
            'title' => 'Hello, world!',
            'config' => 'title',
        ];

        $this->assertSame('Hello, world!', $this->renderString('{{ @$config }}', $data, true));
    }
}
