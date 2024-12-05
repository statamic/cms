<?php

namespace Tests\Antlers\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modifier;
use Statamic\Support\Arr;
use Tests\Antlers\ParserTestCase;

class MethodStyleModifiersTest extends ParserTestCase
{
    public function test_using_method_syntax_works_for_modifiers_with_empty_arg_group()
    {
        $template = <<<'EOT'
{{ title | upper() }}
EOT;

        $this->assertSame('HELLO, WILDERNESS', $this->renderString($template, [
            'title' => 'Hello, Wilderness',
        ], true));
    }

    public function test_ensure_arg_group_is_still_optional()
    {
        $template = <<<'EOT'
{{ title | upper }}
EOT;

        $this->assertSame('HELLO, WILDERNESS', $this->renderString($template, [
            'title' => 'Hello, Wilderness',
        ], true));
    }

    public function test_method_syntax_allows_for_chained_modifiers()
    {
        $template = <<<'EOT'
{{ title | upper() | lower() }}
EOT;

        $this->assertSame('hello, wilderness', $this->renderString($template, [
            'title' => 'Hello, Wilderness',
        ], true));
    }

    public function test_method_syntax_captures_context_variables()
    {
        $template = <<<'EOT'
{{ title | upper() | ensure_right('To the right!') | ensure_left(toTheLeft) }}
EOT;

        $result = $this->renderString($template, [
            'title' => 'The Title.',
            'toTheLeft' => 'The left value.',
        ], true);

        $this->assertSame('The left value.THE TITLE.To the right!', $result);
    }

    public function test_modifiers_can_change_behavior_based_on_modifier_syntax_used()
    {
        // Backwards compatibility test.

        (new class extends Modifier
        {
            public static $handle = 'test_modifier';

            public function index($value, $params, $context)
            {
                $suffix = $params[0];

                // The "{__method_args}" only exists in the context if
                // the Runtime parser created the $params array as
                // a result of processing method-style modifiers.
                // If this key exists, we should not look into
                // the context as the Runtime will handle
                // variables/context interactions for us
                // when using method-style modifiers.

                // If this key does NOT exist, the modifier
                // is executing in one of the following:
                //   - Any version of Statamic <= runtime introduction
                //   - Parameter-style modifiers (any version)
                //   - Shorthand-style modifiers (any version)

                // If we did not do this check here, the Runtime
                // would return `value_three` since it resolved
                // the "value_two" variable reference for us.
                // This would then look up "value_three"
                // within the context, returning the
                // wrong value to our final page.
                if (! array_key_exists('{__method_args}', $context)) {
                    $suffix = Arr::get($context, $params[0], $params[0]);
                }

                return $value.'-'.$suffix;
            }
        })::register();

        $data = [
            'value_one' => 'Value One',
            'value_two' => 'value_three',
            'value_three' => 'I should not be in the output.',
        ];

        $template = <<<'EOT'
<{{ value_one | test_modifier:value_two }}><{{ value_one | test_modifier(value_two) }}>
EOT;

        $this->assertSame('<Value One-value_three><Value One-value_three>', $this->renderString($template, $data, true));

        // If the method-style flag leaks, we should get the
        // wrong value from the shorthand-style modifier.
        $template = <<<'EOT'
{{ test = 'one,two,three'|explode(',') }}<{{ value_one | test_modifier:value_two }}>{{ /test }}
EOT;

        $this->assertSame('<Value One-value_three><Value One-value_three><Value One-value_three>', $this->renderString($template, $data, true));
    }
}
