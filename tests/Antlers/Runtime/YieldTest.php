<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class YieldTest extends ParserTestCase
{
    public function test_normal_yield()
    {
        $template = <<<'EOT'
Before Yield.
{{ yield:dark_mode }}
After Yield.

{{ section:dark_mode }}
<span>Some content.</span>
{{ /section:dark_mode }}
EOT;

        $expected = <<<'EOT'
Before Yield.

<span>Some content.</span>

After Yield.


EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, [], true));
    }

    public function test_content_yielded_in_partial_bubbles_up()
    {
        $template = <<<'EOT'
Before Yield.
{{ yield:dark_mode }}
After Yield.

Before Partial.{{ partial:yieldpartial }}After Partial.
EOT;

        $expected = <<<'EOT'
Before Yield.

<span>Some content.</span>

After Yield.

Before Partial.After Partial.
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, [], true));
    }

    public function test_yield_slots_named_slots_are_not_confused_when_looping_from_tags()
    {
        $template = <<<'EOT'
<ul>
{{ loop from="1" to="10" }}
Before Yield
{{ yield:tester }}
After Yield

Before Partial
{{ partial:nested }}
{{ slot:test }}NameStart{{ value }}NameEnd{{ /slot:test }}

Normal Slot Content ({{ value }})
{{ /partial:nested }}
After Partial
{{ /loop }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>

Before Yield

<p>I am section: 1   </p>

After Yield

Before Partial
<li>
NameStart1NameEnd



SlotBeginNormal Slot Content (1)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 2   </p>

After Yield

Before Partial
<li>
NameStart2NameEnd



SlotBeginNormal Slot Content (2)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 3   </p>

After Yield

Before Partial
<li>
NameStart3NameEnd



SlotBeginNormal Slot Content (3)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 4   </p>

After Yield

Before Partial
<li>
NameStart4NameEnd



SlotBeginNormal Slot Content (4)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 5   </p>

After Yield

Before Partial
<li>
NameStart5NameEnd



SlotBeginNormal Slot Content (5)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 6   </p>

After Yield

Before Partial
<li>
NameStart6NameEnd



SlotBeginNormal Slot Content (6)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 7   </p>

After Yield

Before Partial
<li>
NameStart7NameEnd



SlotBeginNormal Slot Content (7)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 8   </p>

After Yield

Before Partial
<li>
NameStart8NameEnd



SlotBeginNormal Slot Content (8)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 9   </p>

After Yield

Before Partial
<li>
NameStart9NameEnd



SlotBeginNormal Slot Content (9)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 10   </p>

After Yield

Before Partial
<li>
NameStart10NameEnd



SlotBeginNormal Slot Content (10)SlotEnd
</li>
After Partial

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, [], true));
    }

    public function test_each_yield_section_in_a_loop_is_independent()
    {
        $data = [
            'arrdata' => [
                ['value' => 1],
                ['value' => 2],
                ['value' => 3],
            ],
        ];

        $template = <<<'EOT'
<ul>
{{ arrdata }}
Before Yield
{{ yield:tester }}
After Yield

Before Partial
{{ partial:nested }}
{{ slot:test }}NameStart{{ value }}NameEnd{{ /slot:test }}

Normal Slot Content ({{ value }})
{{ /partial:nested }}
After Partial
{{ /arrdata }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>

Before Yield

<p>I am section: 1   </p>

After Yield

Before Partial
<li>
NameStart1NameEnd



SlotBeginNormal Slot Content (1)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 2   </p>

After Yield

Before Partial
<li>
NameStart2NameEnd



SlotBeginNormal Slot Content (2)SlotEnd
</li>
After Partial

Before Yield

<p>I am section: 3   </p>

After Yield

Before Partial
<li>
NameStart3NameEnd



SlotBeginNormal Slot Content (3)SlotEnd
</li>
After Partial

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, $data, true));
    }

    public function test_yield_outside_loop_utilizes_last_iteration()
    {
        $data = [
            'arrdata' => [
                ['value' => 1],
                ['value' => 2],
                ['value' => 3],
            ],
        ];

        $template = <<<'EOT'
Before Yield
{{ yield:tester }}
After Yield

<ul>
{{ arrdata }}
Before Partial
{{ partial:nested }}
{{ slot:test }}NameStart{{ value }}NameEnd{{ /slot:test }}

Normal Slot Content ({{ value }})
{{ /partial:nested }}
After Partial
{{ /arrdata }}
</ul>
EOT;

        $expected = <<<'EOT'
Before Yield

<p>I am section: 3   </p>

After Yield

<ul>

Before Partial
<li>
NameStart1NameEnd



SlotBeginNormal Slot Content (1)SlotEnd
</li>
After Partial

Before Partial
<li>
NameStart2NameEnd



SlotBeginNormal Slot Content (2)SlotEnd
</li>
After Partial

Before Partial
<li>
NameStart3NameEnd



SlotBeginNormal Slot Content (3)SlotEnd
</li>
After Partial

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, $data, true));
    }

    public function test_yield_can_be_used_inside_conditions()
    {
        $template = <<<'EOT'
{{ if {yield:section_name} }}
Hello, universe!
{{ /if }}
EOT;

        $this->assertSame('', trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
{{ section:section_name }}Some content.{{ /section:section_name }}

{{ if {yield:section_name} }}
Hello, universe!
{{ /if }}
EOT;

        $this->assertSame('Hello, universe!', trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
{{ if {yield:section_name} }}
Hello, universe!
{{ /if }}
EOT;

        $this->assertSame('', trim($this->renderString($template, [], true)));
    }

    public function test_condition_blocks_do_not_leak_their_condition_state()
    {
        $template = <<<'EOT'
{{ if false }}
    {{# It doesn't matter whats inside here #}}
{{ else }}
    Else Statement.
{{ /if }}

{{ section:seo_body }}Content{{ section:seo_body }}

{{ yield:seo_body }}
EOT;

        $expected = <<<'EXPECTED'
Else Statement.


Content
EXPECTED;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));
    }
}
