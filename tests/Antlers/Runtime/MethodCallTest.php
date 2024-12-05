<?php

namespace Tests\Antlers\Runtime;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Antlers\Fixtures\MethodClasses\CallCounter;
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

    public function test_method_calls_can_have_modifiers_applied()
    {
        $data = [
            'date' => Carbon::parse('October 1st, 2012'),
        ];

        $template = <<<'EOT'
{{ date }}
{{ date:copy() | modify_date('+1 weeks') }}
{{ date:copy() | modify_date('+2 weeks') }}
{{ date:copy() | modify_date('+3 weeks') }}
{{ date:copy() | modify_date('+4 weeks') }}
{{ date:copy() | modify_date('+5 weeks') }}

{{ date }}
{{ (date:copy()) | modify_date('+1 weeks') }}
{{ (date:copy()) | modify_date('+2 weeks') }}
{{ (date:copy()) | modify_date('+3 weeks') }}
{{ (date:copy()) | modify_date('+4 weeks') }}
{{ (date:copy()) | modify_date('+5 weeks') }}

{{ date }}
{{ (((((date:copy()))))) | modify_date('+1 weeks') }}
{{ (((((date:copy()))))) | modify_date('+2 weeks') }}
{{ (((((date:copy()))))) | modify_date('+3 weeks') }}
{{ (((((date:copy()))))) | modify_date('+4 weeks') }}
{{ (((((date:copy()))))) | modify_date('+5 weeks') }}

{{ date }}
{{ date:copy().modify('+1 weeks') }}
{{ date:copy().modify('+2 weeks') }}
{{ date:copy().modify('+3 weeks') }}
{{ date:copy().modify('+4 weeks') }}
{{ date:copy().modify('+5 weeks') }}

{{ date }}
{{ (date:copy().modify('+1 weeks')) }}
{{ (date:copy().modify('+2 weeks')) }}
{{ (date:copy().modify('+3 weeks')) }}
{{ (date:copy().modify('+4 weeks')) }}
{{ (date:copy().modify('+5 weeks')) }}

{{ date }}
{{ (date:copy().modify('+1 weeks')) }}
{{ (date:copy().modify('+2 weeks')) }}
{{ (date:copy().modify('+3 weeks')) }}
{{ (date:copy().modify('+4 weeks')) }}
{{ (date:copy().modify('+5 weeks')) }}

{{ date }}
{{ ((((date:copy().modify('+1 weeks'))))) }}
{{ ((((date:copy().modify('+2 weeks'))))) }}
{{ ((((date:copy().modify('+3 weeks'))))) }}
{{ ((((date:copy().modify('+4 weeks'))))) }}
{{ ((((date:copy().modify('+5 weeks'))))) }}
EOT;

        $expected = <<<'EOT'
2012-10-01 00:00:00
2012-10-08 00:00:00
2012-10-15 00:00:00
2012-10-22 00:00:00
2012-10-29 00:00:00
2012-11-05 00:00:00

2012-10-01 00:00:00
2012-10-08 00:00:00
2012-10-15 00:00:00
2012-10-22 00:00:00
2012-10-29 00:00:00
2012-11-05 00:00:00

2012-10-01 00:00:00
2012-10-08 00:00:00
2012-10-15 00:00:00
2012-10-22 00:00:00
2012-10-29 00:00:00
2012-11-05 00:00:00

2012-10-01 00:00:00
2012-10-08 00:00:00
2012-10-15 00:00:00
2012-10-22 00:00:00
2012-10-29 00:00:00
2012-11-05 00:00:00

2012-10-01 00:00:00
2012-10-08 00:00:00
2012-10-15 00:00:00
2012-10-22 00:00:00
2012-10-29 00:00:00
2012-11-05 00:00:00

2012-10-01 00:00:00
2012-10-08 00:00:00
2012-10-15 00:00:00
2012-10-22 00:00:00
2012-10-29 00:00:00
2012-11-05 00:00:00

2012-10-01 00:00:00
2012-10-08 00:00:00
2012-10-15 00:00:00
2012-10-22 00:00:00
2012-10-29 00:00:00
2012-11-05 00:00:00
EOT;

        $this->assertSame($expected, trim($this->renderString($template, $data, true)));
    }

    public function test_method_calls_not_get_called_more_than_declared()
    {
        $counter = new CallCounter();

        $template = <<<'EOT'
{{ counter:increment():increment():increment() }}
EOT;

        $this->assertSame('Count: 3', $this->renderString($template, ['counter' => $counter]));
    }

    public function test_dangling_chained_method_calls()
    {
        $template = <<<'ANTLERS'
{{
    datetime:parse("October 12, 2001"):
            addDays(10):
            toAtomString()
}}
ANTLERS;
        $result = $this->renderString($template, ['datetime' => new TestDateTime]);

        $this->assertSame('2001-10-22T00:00:00+00:00', $result);
    }
}

class TestDateTime
{
    public function parse($string)
    {
        return Carbon::parse($string);
    }
}
