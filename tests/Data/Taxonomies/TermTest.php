<?php

namespace Tests\Data\Taxonomies;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Event;
use Mockery;
use Statamic\Events\TermBlueprintFound;
use Statamic\Events\TermCreated;
use Statamic\Events\TermSaved;
use Statamic\Events\TermSaving;
use Statamic\Facades;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Blueprint;
use Statamic\Taxonomies\Taxonomy as TaxonomiesTaxonomy;
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
    public function it_dispatches_an_event_when_getting_blueprint()
    {
        Event::fake();

        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'blueprint' => $blueprint = (new Blueprint)->setHandle('blueprint'),
        ]));
        $taxonomy = tap(Taxonomy::make('tags'))->save();
        $term = (new Term)->taxonomy($taxonomy);

        // Do it twice so we can check the event is only dispatched once.
        $term->blueprint();
        $term->blueprint();

        Event::assertDispatchedTimes(TermBlueprintFound::class, 1);
        Event::assertDispatched(TermBlueprintFound::class, function ($event) use ($blueprint, $term) {
            return $event->blueprint === $blueprint
                && $event->term === $term;
        });
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
    public function it_saves_through_the_api()
    {
        Event::fake();

        $taxonomy = (new TaxonomiesTaxonomy)->handle('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('foo')->data(['foo' => 'bar']);

        $return = $term->save();

        $this->assertTrue($return);

        Event::assertDispatched(TermSaving::class, function ($event) use ($term) {
            return $event->term === $term;
        });

        Event::assertDispatched(TermCreated::class, function ($event) use ($term) {
            return $event->term === $term;
        });

        Event::assertDispatched(TermSaved::class, function ($event) use ($term) {
            return $event->term === $term;
        });
    }

    /** @test */
    public function it_dispatches_term_created_only_once()
    {
        Event::fake();

        $taxonomy = (new TaxonomiesTaxonomy)->handle('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('foo')->data(['foo' => 'bar']);

        Facades\Term::shouldReceive('save')->with($term);
        Facades\Term::shouldReceive('find')->with($term->id())->times(3)->andReturn(null, $term, $term);

        $term->save();
        $term->save();
        $term->save();

        Event::assertDispatched(TermSaved::class, 3);
        Event::assertDispatched(TermCreated::class, 1);
    }

    /** @test */
    public function it_saves_quietly()
    {
        Event::fake();

        $taxonomy = (new TaxonomiesTaxonomy)->handle('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('foo')->data(['foo' => 'bar']);

        $return = $term->saveQuietly();

        $this->assertTrue($return);

        Event::assertNotDispatched(TermSaving::class);
        Event::assertNotDispatched(TermSaved::class);
        Event::assertNotDispatched(TermCreated::class);
    }

    /** @test */
    public function if_saving_event_returns_false_the_term_doesnt_save()
    {
        Event::fake([TermSaved::class]);

        Event::listen(TermSaving::class, function () {
            return false;
        });

        $taxonomy = (new TaxonomiesTaxonomy)->handle('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('foo')->data(['foo' => 'bar']);

        $return = $term->save();

        $this->assertFalse($return);

        Event::assertNotDispatched(TermSaved::class);
    }

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
            ['label' => 'Index', 'format' => 'http://preview.com/{locale}/tags?preview=true', 'refresh' => true],
            ['label' => 'Show', 'format' => 'http://preview.com/{locale}/tags/{slug}?preview=true', 'refresh' => true],
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
