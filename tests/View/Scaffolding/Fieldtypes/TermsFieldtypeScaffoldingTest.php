<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class TermsFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    use PreventSavingStacheItemsToDisk;

    protected array $field = [
        'type' => 'terms',
    ];

    #[Test]
    public function it_scaffolds_terms_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    {{ url /}}
    {{ title /}}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_terms_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ url /}}
    {{ title /}}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_terms_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $test_item)
    {{ $test_item->url }}
    {{ $test_item->title }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_terms_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $test_item)
    {{ $test_item->url }}
    {{ $test_item->title }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_hierarchical_terms_fieldtype_antlers()
    {
        Taxonomy::make('categories')->structureContents([])->save();

        $result = $this->scaffoldAntlersField($this->field(['taxonomies' => ['categories']]));

        $expected = <<<'EXPECTED'
{{ test }}
    {{ url /}}
    {{ title /}}
    {{ ancestors }}
        {{ url /}}
        {{ title /}}
    {{ /ancestors }}
    {{ children }}
        {{ url /}}
        {{ title /}}
    {{ /children }}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_hierarchical_terms_fieldtype_blade()
    {
        Taxonomy::make('categories')->structureContents([])->save();

        $result = $this->scaffoldBladeField($this->field(['taxonomies' => ['categories']]));

        $expected = <<<'EXPECTED'
@foreach ($test as $test_item)
    {{ $test_item->url }}
    {{ $test_item->title }}
    @foreach ($test_item->ancestors as $ancestor)
        {{ $ancestor->url }}
        {{ $ancestor->title }}
    @endforeach
    @foreach ($test_item->children as $child)
        {{ $child->url }}
        {{ $child->title }}
    @endforeach
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
