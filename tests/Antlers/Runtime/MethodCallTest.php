<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\Fixtures\MethodClasses\ClassOne;
use Tests\Antlers\Fixtures\MethodClasses\StringLengthObject;
use Tests\Antlers\ParserTestCase;

class MethodCallTest extends ParserTestCase
{
    public function test_methods_can_be_called()
    {
        $object = new ClassOne();

        $this->assertSame('Value: hello', $this->renderString('{{ object:method("hello"):methodTwo() }}', [
            'object' => $object,
        ]));
        $this->assertSame('String: hello', $this->renderString('{{ object:method("hello") }}', [
            'object' => $object,
        ]));
    }

    public function test_chained_methods_colon_syntax()
    {
        $object = new ClassOne();

        $this->assertSame('Value: hello', $this->renderString('{{ object:method("hello"):methodTwo() }}', [
            'object' => $object,
        ]));
    }

    public function test_chained_methods_dot_syntax()
    {
        $object = new ClassOne();

        $this->assertSame('Value: hello', $this->renderString('{{ object.method("hello").methodTwo() }}', [
            'object' => $object,
        ]));
    }

    public function test_chained_methods_mixed_syntax()
    {
        $object = new ClassOne();

        $this->assertSame('Value: hello', $this->renderString('{{ object:method("hello").methodTwo() }}', [
            'object' => $object,
        ]));
    }

    public function test_method_calls_can_be_used_within_conditions_without_explicit_logic_groups()
    {
        // This test ensures that the language parser will automatically insert logic groups
        // pairs around method calls so that they can be used without the developer doing so.
        $data = [
            'title' => new StringLengthObject('Hello'),
        ];

        $template = <<<'EOT'
{{ if title && title:length() < 15 }}Yes{{ else }}No{{ endif }}
EOT;

        $this->assertSame('Yes', $this->renderString($template, $data));
    }

    public function test_method_calls_can_be_used_within_conditions_without_explicit_logic_groups_dot_syntax()
    {
        // This test ensures that the language parser will automatically insert logic groups
        // pairs around method calls so that they can be used without the developer doing so.
        $data = [
            'title' => new StringLengthObject('Hello'),
        ];

        $template = <<<'EOT'
{{ if title && title.length() < 15 }}Yes{{ else }}No{{ endif }}
EOT;

        $this->assertSame('Yes', $this->renderString($template, $data));
    }

    public function test_method_calls_can_be_used_within_conditions_without_explicit_logic_groups_arrow_syntax()
    {
        // This test ensures that the language parser will automatically insert logic groups
        // pairs around method calls so that they can be used without the developer doing so.
        $data = [
            'title' => new StringLengthObject('Hello'),
        ];

        $template = <<<'EOT'
{{ if title && title->length() < 15 }}Yes{{ else }}No{{ endif }}
EOT;

        $this->assertSame('Yes', $this->renderString($template, $data));
    }

    public function test_method_calls_can_be_used_within_conditions_without_explicit_logic_groups_arrow_syntax_with_strict_var()
    {
        // This test ensures that the language parser will automatically insert logic groups
        // pairs around method calls so that they can be used without the developer doing so.
        $data = [
            'title' => new StringLengthObject('Hello'),
        ];

        $template = <<<'EOT'
{{ if title && $title->length() < 15 }}Yes{{ else }}No{{ endif }}
EOT;

        $this->assertSame('Yes', $this->renderString($template, $data));
    }
}
