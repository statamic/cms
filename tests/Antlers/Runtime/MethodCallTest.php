<?php

namespace Tests\Antlers\Runtime;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Text;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Tests\Antlers\Fixtures\MethodClasses\CallCounter;
use Tests\Antlers\Fixtures\MethodClasses\ClassOne;
use Tests\Antlers\Fixtures\MethodClasses\StringLengthObject;
use Tests\Antlers\ParserTestCase;

class MethodCallTest extends ParserTestCase
{
    public function tearDown(): void
    {
        GlobalRuntimeState::$throwErrorOnAccessViolation = false;
        GlobalRuntimeState::$allowMethodsInContent = false;
        GlobalRuntimeState::$isEvaluatingUserData = false;
        GlobalRuntimeState::$isEvaluatingData = false;

        parent::tearDown();
    }

    public function test_methods_can_be_called()
    {
        $object = new ClassOne();

        $this->assertSame('Value: hello', $this->renderString('{{ object:method("hello"):methodTwo() }}', [
            'object' => $object,
        ], false, true));
        $this->assertSame('String: hello', $this->renderString('{{ object:method("hello") }}', [
            'object' => $object,
        ], false, true));
    }

    public function test_chained_methods_colon_syntax()
    {
        $object = new ClassOne();

        $this->assertSame('Value: hello', $this->renderString('{{ object:method("hello"):methodTwo() }}', [
            'object' => $object,
        ], false, true));
    }

    public function test_chained_methods_dot_syntax()
    {
        $object = new ClassOne();

        $this->assertSame('Value: hello', $this->renderString('{{ object.method("hello").methodTwo() }}', [
            'object' => $object,
        ], false, true));
    }

    public function test_chained_methods_mixed_syntax()
    {
        $object = new ClassOne();

        $this->assertSame('Value: hello', $this->renderString('{{ object:method("hello").methodTwo() }}', [
            'object' => $object,
        ], false, true));
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

        $this->assertSame('Yes', $this->renderString($template, $data, false, true));
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

        $this->assertSame('Yes', $this->renderString($template, $data, false, true));
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

        $this->assertSame('Yes', $this->renderString($template, $data, false, true));
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

        $this->assertSame('Yes', $this->renderString($template, $data, false, true));
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

        $this->assertSame($expected, trim($this->renderString($template, $data, true, true)));
    }

    public function test_method_calls_not_get_called_more_than_declared()
    {
        $counter = new CallCounter();

        $template = <<<'EOT'
{{ counter:increment():increment():increment() }}
EOT;

        $this->assertSame('Count: 3', $this->renderString($template, ['counter' => $counter], false, true));
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
        $result = $this->renderString($template, ['datetime' => new TestDateTime], false, true);

        $this->assertSame('2001-10-22T00:00:00+00:00', $result);
    }

    public function test_method_calls_blocked_in_user_content()
    {
        $textFieldtype = new Text();
        $field = new Field('text_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textFieldtype->setField($field);
        $object = new ClassOne();
        $value = new Value('{{ object:method("hello") }}', 'text_field', $textFieldtype);

        Log::shouldReceive('warning')
            ->once()
            ->with('Method call evaluated in user content.', \Mockery::type('array'));

        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'object' => $object,
        ], false, true);

        $this->assertSame('', $result);
    }

    public function test_method_calls_allowed_in_user_content_when_configured()
    {
        GlobalRuntimeState::$allowMethodsInContent = true;

        $textFieldtype = new Text();
        $field = new Field('text_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textFieldtype->setField($field);
        $object = new ClassOne();
        $value = new Value('{{ object:method("hello") }}', 'text_field', $textFieldtype);

        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'object' => $object,
        ], false, true);

        $this->assertSame('String: hello', $result);

        GlobalRuntimeState::$allowMethodsInContent = false;
    }

    public function test_method_calls_in_user_content_throw_when_configured()
    {
        GlobalRuntimeState::$throwErrorOnAccessViolation = true;

        $textFieldtype = new Text();
        $field = new Field('text_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textFieldtype->setField($field);
        $object = new ClassOne();
        $value = new Value('{{ object:method("hello") }}', 'text_field', $textFieldtype);

        $this->expectException(RuntimeException::class);

        $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'object' => $object,
        ], false, true);

        GlobalRuntimeState::$throwErrorOnAccessViolation = false;
    }

    public function test_method_calls_still_work_in_templates()
    {
        $object = new ClassOne();

        $this->assertSame('String: hello', $this->renderString('{{ object:method("hello") }}', [
            'object' => $object,
        ], false, true));
    }

    public function test_nested_value_does_not_reset_user_data_flag()
    {
        $textFieldtype = new Text();

        $nestedField = new Field('nested_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textFieldtype->setField($nestedField);
        $nestedValue = new Value('Hello', 'nested_field', $textFieldtype);

        $outerField = new Field('outer_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textFieldtype->setField($outerField);
        $object = new ClassOne();
        $outerValue = new Value('{{ nested_field }}{{ object:method("hello") }}', 'outer_field', $textFieldtype);

        Log::shouldReceive('warning')
            ->once()
            ->with('Method call evaluated in user content.', \Mockery::type('array'));

        $result = $this->renderString('{{ outer_field }}', [
            'outer_field' => $outerValue,
            'nested_field' => $nestedValue,
            'object' => $object,
        ], false, true);

        $this->assertSame('Hello', $result);
    }
}

class TestDateTime
{
    public function parse($string)
    {
        return Carbon::parse($string);
    }
}
