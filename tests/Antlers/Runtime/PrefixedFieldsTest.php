<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class PrefixedFieldsTest extends ParserTestCase
{
    protected $data = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'title' => 'Initial Title',
            'var' => 'Initial Value',
            'other' => 'Initial Other',
            'initial' => 'Initial Only',
            'test_title' => 'Test: Title',
            'test_var' => 'Test: Value',
            'test_other' => 'Test: Other',
            'prefix_title' => 'Prefix: Title',
            'prefix_var' => 'Prefix: Value',
            'array' => ['one', 'two', 'three'],
            'prefix_array' => ['four', 'five', 'six'],
        ];
    }

    public function test_scope_prefixes_work_inside_conditions()
    {
        $template = <<<'EOT'
{{ scope handle_prefix="prefix_" }}
<Title: {{ title }}><Condition: {{ if title }}{{title}}{{ /if }}{{ /scope }}>
EOT;

        $this->assertSame(
            '<Title: Prefix: Title><Condition: Prefix: Title>',
            trim($this->renderString($template, $this->data, true))
        );
    }

    public function test_scope_prefixes_can_apply_to_array_vars()
    {
        $template = <<<'EOT'
{{ array.1 }}
{{ array }}<{{ value }}>{{ /array }}

{{ scope handle_prefix="prefix_" }}
{{ array.0 }}
{{ array }}<{{ value }}>{{ /array }}
{{ array | reverse }}<{{ value }}>{{ /array }}
{{ /scope }}
EOT;

        $expected = <<<'EOT'
two
<one><two><three>


four
<four><five><six>
<six><five><four>

EOT;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->renderString($template, $this->data, true)
        );
    }

    public function test_partials_respect_field_prefixes()
    {
        $template = <<<'EOT'
<{{ title }}><{{ var }}><{{ other }}><{{ initial }}>
{{ partial:prefixed handle_prefix="prefix_" }}
<{{ title }}><{{ var }}><{{ other }}><{{ initial }}>

{{ partial:prefixed handle_prefix="test_" }}
<{{ title }}><{{ var }}><{{ other }}><{{ initial }}>

{{ partial:prefixed :handle_prefix="null" }}
RESET BEFORE: <{{ title }}><{{ var }}><{{ other }}><{{ initial }}>

{{ partial:prefixed handle_prefix="prefix_" }}
NESTED IN RESET: <{{ title }}><{{ var }}><{{ other }}><{{ initial }}>
{{ /partial:prefixed }}

RESET AFTER: <{{ title }}><{{ var }}><{{ other }}><{{ initial }}>
{{ /partial:prefixed }}

<{{ title }}><{{ var }}><{{ other }}><{{ initial }}>
{{ /partial:prefixed }}

<{{ title }}><{{ var }}><{{ other }}><{{ initial }}>
{{ /partial:prefixed }}
<{{ title }}><{{ var }}><{{ other }}><{{ initial }}>
EOT;

        $expected = <<<'EOT'
<Initial Title><Initial Value><Initial Other><Initial Only>
<Prefix: Title><Prefix: Value><Initial Other><Initial Only>

<Test: Title><Test: Value><Test: Other><Initial Only>

RESET BEFORE: <Initial Title><Initial Value><Initial Other><Initial Only>

NESTED IN RESET: <Prefix: Title><Prefix: Value><Initial Other><Initial Only>

RESET AFTER: <Initial Title><Initial Value><Initial Other><Initial Only>

<Test: Title><Test: Value><Test: Other><Initial Only>

<Prefix: Title><Prefix: Value><Initial Other><Initial Only>
<Initial Title><Initial Value><Initial Other><Initial Only>
EOT;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->renderString($template, $this->data, true)
        );
    }

    public function test_scope_tag_can_prefix_fields()
    {
        $template = <<<'EOT'
<{{ title }}><{{ scope handle_prefix="test_" }}{{ title }}{{ /scope }}><{{ title }}><{{ scope handle_prefix="prefix_" }}{{ title }}{{ /scope }}><{{ title }}>
EOT;

        $this->assertSame('<Initial Title><Test: Title><Initial Title><Prefix: Title><Initial Title>', $this->renderString($template, $this->data, true));
    }

    public function test_prefixes_can_receive_arrays()
    {
        $template = <<<'EOT'
{{ title }}
{{ var }}
{{ other }}

{{ scope :handle_prefix="['prefix_', 'test_']" }}
{{ title }}
{{ var }}
{{ other }}
{{ /scope }}

{{ title }}
{{ var }}
{{ other }}
EOT;

        // Ensures the prefix we specified wins if it matches first.
        $expected = <<<'EOT'
Initial Title
Initial Value
Initial Other


Prefix: Title
Prefix: Value
Test: Other


Initial Title
Initial Value
Initial Other
EOT;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->renderString($template, $this->data, true)
        );

        $template = <<<'EOT'
{{ title }}
{{ var }}
{{ other }}

{{ scope :handle_prefix="['test_', 'prefix_']" }}
{{ title }}
{{ var }}
{{ other }}
{{ /scope }}

{{ title }}
{{ var }}
{{ other }}
EOT;

        // Ensures the prefix we specified wins if it matches first.
        $expected = <<<'EOT'
Initial Title
Initial Value
Initial Other


Test: Title
Test: Value
Test: Other


Initial Title
Initial Value
Initial Other
EOT;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->renderString($template, $this->data, true)
        );
    }

    public function test_prefix_can_be_built_dynamically()
    {
        $template = <<<'EOT'
{{ title }}
{{ var }}
{{ other }}

{{ scope :handle_prefix="prefix ?= prefix + '_'" }}
{{ title }}
{{ var }}
{{ other }}
{{ /scope }}

{{ title }}
{{ var }}
{{ other }}
EOT;

        $expected = <<<'EOT'
Initial Title
Initial Value
Initial Other


Test: Title
Test: Value
Test: Other


Initial Title
Initial Value
Initial Other
EOT;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->renderString($template, array_merge($this->data, [
                'prefix' => 'test',
            ]), true)
        );
    }

    public function test_prefix_can_be_assigned_conditionally()
    {
        $template = <<<'EOT'
{{ title }}
{{ var }}
{{ other }}

{{ scope :handle_prefix="prefix ?= prefix" }}
{{ title }}
{{ var }}
{{ other }}
{{ /scope }}

{{ title }}
{{ var }}
{{ other }}
EOT;

        $expected = <<<'EOT'
Initial Title
Initial Value
Initial Other


Test: Title
Test: Value
Test: Other


Initial Title
Initial Value
Initial Other
EOT;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->renderString($template, array_merge($this->data, [
                'prefix' => 'test_',
            ]), true)
        );

        $expected = <<<'EOT'
Initial Title
Initial Value
Initial Other


Initial Title
Initial Value
Initial Other


Initial Title
Initial Value
Initial Other
EOT;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->renderString($template, array_merge($this->data, [
                'prefix' => 'not_a_valid_prefix_value_',
            ]), true)
        );
    }

    public function test_nested_prefixes_uses_most_recent_first()
    {
        $template = <<<'EOT'
{{ title }}
{{ var }}
{{ other }}

{{ scope handle_prefix="test_" }}
{{ title }}
{{ var }}
{{ other }}

{{ scope handle_prefix="prefix_" }}
{{ title }}
{{ var }}
{{ other }}
{{ /scope }}

{{ /scope }}

{{ title }}
{{ var }}
{{ other }}
EOT;

        $expected = <<<'EOT'
Initial Title
Initial Value
Initial Other


Test: Title
Test: Value
Test: Other


Prefix: Title
Prefix: Value
Test: Other




Initial Title
Initial Value
Initial Other
EOT;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->renderString($template, $this->data, true),
        );
    }
}
