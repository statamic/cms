<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class CodeFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'code',
    ];

    #[Test]
    public function it_scaffolds_code_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
<pre class="language-{{ mode }}">
    <code>{{ code }}</code>
</pre>
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_code_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $this->assertStringContainsString('root:nested_group:test', $result);
        $this->assertStringContainsString('/root:nested_group:test', $result);
    }

    #[Test]
    public function it_scaffolds_code_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
<pre class="language-{{ $test['mode'] }}">
    <code>{!! $test['code'] !!}</code>
</pre>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_code_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $this->assertStringContainsString('$root->nested_group->test[\'mode\']', $result);
        $this->assertStringContainsString('$root->nested_group->test[\'code\']', $result);
    }
}
