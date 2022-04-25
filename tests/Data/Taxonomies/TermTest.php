<?php

namespace Tests\Data\Taxonomies;

use Facades\Statamic\Fields\BlueprintRepository;
use Mockery;
use Statamic\Facades;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Blueprint;
use Statamic\Taxonomies\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_the_blueprint_when_defined_on_itself()
    {
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        Taxonomy::make('tags')->save();
        $term = (new Term)->taxonomy('tags')->blueprint('second');

        $this->assertSame($second, $term->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function it_gets_the_blueprint_when_defined_in_a_value()
    {
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        Taxonomy::make('tags')->save();
        $term = (new Term)->taxonomy('tags')->set('blueprint', 'second');

        $this->assertSame($second, $term->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function it_gets_the_default_taxonomy_blueprint_when_undefined()
    {
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        $taxonomy = tap(Taxonomy::make('tags'))->save();
        $term = (new Term)->taxonomy($taxonomy);

        $this->assertSame($first, $term->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function the_blueprint_is_blinked_when_getting_and_flushed_when_setting()
    {
        $term = (new Term)->taxonomy('tags');
        $taxonomy = Mockery::mock(Taxonomy::make('tags'));
        $taxonomy->shouldReceive('termBlueprint')->with(null, $term)->once()->andReturn('the old blueprint');
        $taxonomy->shouldReceive('termBlueprint')->with('new', $term)->once()->andReturn('the new blueprint');
        Taxonomy::shouldReceive('findByHandle')->with('tags')->andReturn($taxonomy);

        $this->assertEquals('the old blueprint', $term->blueprint());
        $this->assertEquals('the old blueprint', $term->blueprint());

        $term->blueprint('new');

        $this->assertEquals('the new blueprint', $term->blueprint());
        $this->assertEquals('the new blueprint', $term->blueprint());
    }

    /** @test */
    public function it_gets_the_entry_count_through_the_repository()
    {
        $term = (new Term)->taxonomy('tags')->slug('foo');

        $mock = \Mockery::mock(Facades\Term::getFacadeRoot())->makePartial();
        Facades\Term::swap($mock);
        $mock->shouldReceive('entriesCount')->with($term)->andReturn(7)->once();

        $this->assertEquals(7, $term->entriesCount());
        $this->assertEquals(7, $term->entriesCount());
    }

    /** @test */
    public function it_gets_file_contents_for_saving()
    {
        tap(Taxonomy::make('tags')->sites(['en', 'fr']))->save();

        $term = (new Term)
            ->taxonomy('tags')
            ->slug('test');

        $term->dataForLocale('en', [
            'title' => 'The title',
            'array' => ['first one', 'second one'],
            'null' => null, // this...
            'empty' => [],  // and this should get stripped out because it's the root. there's no origin to fall back to.
            'content' => 'The content',
        ]);

        $term->dataForLocale('fr', [
            'title' => 'Le titre',
            'array' => ['premier', 'deuxième'],
            'null' => null, // this...
            'empty' => [],  // and this should not get stripped out, otherwise it would fall back to the origin.
            'content' => 'Le contenu',
        ]);

        $this->assertEquals([
            'title' => 'The title',
            'array' => [
                'first one',
                'second one',
            ],
            'content' => 'The content',
            'localizations' => [
                'fr' => [
                    'title' => 'Le titre',
                    'array' => ['premier', 'deuxième'],
                    'null' => null,
                    'empty' => [],
                    'content' => 'Le contenu',
                ],
            ],
        ], $term->fileData());
    }

    /** @test */
    public function it_gets_preview_targets()
    {
        Facades\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.de/'],
        ]]);

        $taxonomy = tap(Taxonomy::make('tags')->sites(['en', 'fr', 'de']))->save();

        $term = (new Term)->taxonomy('tags');

        $termEn = $term->in('en')->slug('foo');
        $termFr = $term->in('fr')->slug('le-foo');
        $termDe = $term->in('de')->slug('das-foo');

        $this->assertEquals([
            ['label' => 'Term', 'format' => '{permalink}', 'url' => 'http://domain.com/tags/foo'],
        ], $termEn->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Term', 'format' => '{permalink}', 'url' => 'http://domain.com/fr/tags/le-foo'],
        ], $termFr->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Term', 'format' => '{permalink}', 'url' => 'http://domain.de/tags/das-foo'],
        ], $termDe->previewTargets()->all());

        $taxonomy->previewTargets([
            ['label' => 'Index', 'format' => 'http://preview.com/{locale}/tags?preview=true'],
            ['label' => 'Show', 'format' => 'http://preview.com/{locale}/tags/{slug}?preview=true'],
        ])->save();

        $this->assertEquals([
            ['label' => 'Index', 'format' => 'http://preview.com/{locale}/tags?preview=true', 'url' => 'http://preview.com/en/tags?preview=true'],
            ['label' => 'Show', 'format' => 'http://preview.com/{locale}/tags/{slug}?preview=true', 'url' => 'http://preview.com/en/tags/foo?preview=true'],
        ], $termEn->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Index', 'format' => 'http://preview.com/{locale}/tags?preview=true', 'url' => 'http://preview.com/fr/tags?preview=true'],
            ['label' => 'Show', 'format' => 'http://preview.com/{locale}/tags/{slug}?preview=true', 'url' => 'http://preview.com/fr/tags/le-foo?preview=true'],
        ], $termFr->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Index', 'format' => 'http://preview.com/{locale}/tags?preview=true', 'url' => 'http://preview.com/de/tags?preview=true'],
            ['label' => 'Show', 'format' => 'http://preview.com/{locale}/tags/{slug}?preview=true', 'url' => 'http://preview.com/de/tags/das-foo?preview=true'],
        ], $termDe->previewTargets()->all());
    }
}
