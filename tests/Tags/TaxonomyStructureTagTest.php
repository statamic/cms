<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TaxonomyStructureTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        tap(Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'calico', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'furniture'],
        ])->save();
    }

    private function parse(string $template): string
    {
        return (string) Antlers::parse($template, [], true);
    }

    #[Test]
    public function it_renders_a_taxonomy_tree()
    {
        $template = '{{ structure:taxonomy:categories }}[{{ title }}{{ if children }}{{ *recursive children* }}{{ /if }}]{{ /structure:taxonomy:categories }}';

        $this->assertEquals('[Animals[Cat[Calico]]][Furniture]', $this->parse($template));
    }

    #[Test]
    public function it_renders_a_taxonomy_tree_using_the_for_parameter()
    {
        $this->assertEquals('[Animals][Furniture]', $this->parse(
            '{{ structure for="taxonomy::categories" }}[{{ title }}]{{ /structure }}'
        ));
    }

    #[Test]
    public function it_renders_a_taxonomy_tree_using_the_nav_tag()
    {
        $this->assertEquals('[Animals][Furniture]', $this->parse(
            '{{ nav:taxonomy:categories }}[{{ title }}]{{ /nav:taxonomy:categories }}'
        ));
    }

    #[Test]
    public function it_starts_from_a_term_slug()
    {
        $template = '{{ structure:taxonomy:categories from="animals" }}[{{ title }}{{ if children }}{{ *recursive children* }}{{ /if }}]{{ /structure:taxonomy:categories }}';

        $this->assertEquals('[Cat[Calico]]', $this->parse($template));
    }

    #[Test]
    public function it_limits_max_depth()
    {
        $template = '{{ structure:taxonomy:categories max_depth="1" }}[{{ title }}{{ if children }}{{ *recursive children* }}{{ /if }}]{{ /structure:taxonomy:categories }}';

        $this->assertEquals('[Animals][Furniture]', $this->parse($template));
    }

    #[Test]
    public function it_sets_is_current_and_is_parent()
    {
        $template = '{{ structure:taxonomy:categories }}[{{ title }}{{ if is_parent }}=parent{{ /if }}{{ if is_current }}=current{{ /if }}{{ if children }}{{ *recursive children* }}{{ /if }}]{{ /structure:taxonomy:categories }}';

        $mock = \Mockery::mock(\Statamic\Facades\URL::getFacadeRoot())->makePartial();
        \Statamic\Facades\URL::swap($mock);

        $mock->shouldReceive('getCurrent')->once()->andReturn(Term::find('categories::cat')->url());

        $this->assertEquals('[Animals=parent[Cat=current[Calico]]][Furniture]', $this->parse($template));
    }

    #[Test]
    public function it_localizes_titles()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        Term::find('categories::animals')->in('fr')->data(['title' => 'Animaux'])->save();

        Site::setCurrent('fr');

        $this->assertEquals('[Animaux][Furniture]', $this->parse(
            '{{ structure:taxonomy:categories }}[{{ title }}]{{ /structure:taxonomy:categories }}'
        ));
    }
}
