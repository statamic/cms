<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Support\Arr;
use Tests\Antlers\ParserTestCase;

class StressTest extends ParserTestCase
{
    public function test_inner_if_is_evaluated_correctly()
    {
        // The inner if that actually returns data is nested inside 500 other if statements.

        $this->assertSame('no', trim($this->renderString($this->getTemplate('five_hundred_nested_ifs'))));
    }

    public function test_deeply_nested_paths_resolve_correctly()
    {
        // The parser doesn't support breaking variable name paths like this across many lines.
        $template = <<<'EOT'
{{ test['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test']['test'] }}
EOT;

        $data = [];
        $arrayPath = str_repeat('test.', 300).'test';
        Arr::set($data, $arrayPath, 'Hello, wilderness');

        $this->assertSame('Hello, wilderness', $this->renderString($template, $data));
    }

    public function test_runtime_and_data_manager_can_parse_and_evaluate_long_paths_dot_syntax()
    {
        // Creates a variable name with dot notation that contains 252 parts.
        $bigVarName = str_repeat('test.', 251).'test';
        $data = [];

        // Constructs the nested array with 252 levels.
        Arr::set($data, $bigVarName, 'Hello, wilderness');

        $this->assertSame('Hello, wilderness', $this->renderString('{{ '.$bigVarName.' }}', $data));
    }

    public function test_runtime_and_data_manager_can_parse_and_evaluate_long_paths_colon_syntax()
    {
        // Creates a variable name with dot notation that contains 252 parts.
        $bigVarName = str_repeat('test:', 251).'test';
        $data = [];

        // Constructs the nested array with 252 levels. Arr set expects dots, so we will
        // make a new version of the bigVarName path with dots to give Arr::set().
        $arrayPath = str_repeat('test.', 251).'test';
        Arr::set($data, $arrayPath, 'Hello, wilderness');

        $this->assertSame('Hello, wilderness', $this->renderString('{{ '.$bigVarName.' }}', $data));
    }
}
