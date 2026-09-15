<?php

namespace Tests\View\Scaffolding;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Scaffolding\TemplateGenerator;
use Tests\PreventSavingStacheItemsToDisk;

class TaxonomyScaffoldingTest extends ScaffoldingTestCase
{
    use PreventSavingStacheItemsToDisk;

    private function scaffold(string $template, $taxonomy, string $language = 'antlers'): string
    {
        $generator = (new TemplateGenerator)->withCoreGenerators();

        if ($language === 'blade') {
            $generator->templateLanguage('blade');
        }

        $this->configureTemplateGenerator($generator);

        return StringUtilities::normalizeLineEndings(
            $generator->scaffold($template, ['taxonomy' => $taxonomy])->content()
        );
    }

    #[Test]
    public function it_scaffolds_a_flat_taxonomy_index()
    {
        $taxonomy = tap(Taxonomy::make('tags')->title('Tags'))->save();

        $expected = <<<'EXPECTED'
<ul>
    {{ taxonomy from="tags" }}
        <li><a href="{{ url /}}">{{ title /}}</a></li>
    {{ /taxonomy }}
</ul>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->scaffold('taxonomy.index', $taxonomy)
        );
    }

    #[Test]
    public function it_scaffolds_a_hierarchical_taxonomy_index()
    {
        $taxonomy = tap(Taxonomy::make('categories')->title('Categories')->structureContents([]))->save();

        $expected = <<<'EXPECTED'
<ul>
    {{ structure:taxonomy:categories }}
        <li><a href="{{ url /}}">{{ title /}}</a></li>
    {{ /structure:taxonomy:categories }}
</ul>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $this->scaffold('taxonomy.index', $taxonomy)
        );
    }

    #[Test]
    public function it_scaffolds_a_hierarchical_taxonomy_show_template()
    {
        $taxonomy = tap(Taxonomy::make('categories')->title('Categories')->structureContents([]))->save();

        $result = $this->scaffold('taxonomy.show', $taxonomy);

        $this->assertStringContainsString('{{ ancestors }}', $result);
        $this->assertStringContainsString('{{ children }}', $result);
        $this->assertStringContainsString('{{ entries with_descendants="true" }}', $result);
    }
}
