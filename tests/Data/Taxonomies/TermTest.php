<?php

namespace Tests\Data\Taxonomies;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\TermBlueprintFound;
use Statamic\Events\TermCreated;
use Statamic\Events\TermCreating;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermDeleting;
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_gets_the_entry_count_through_the_repository()
    {
        $term = (new Term)->taxonomy('tags')->slug('foo');

        $mock = \Mockery::mock(Facades\Term::getFacadeRoot())->makePartial();
        Facades\Term::swap($mock);
        $mock->shouldReceive('entriesCount')->with($term)->andReturn(7)->once();

        $this->assertEquals(7, $term->entriesCount());
        $this->assertEquals(7, $term->entriesCount());
    }

    #[Test]
    public function it_saves_through_the_api()
    {
        Event::fake();

        $taxonomy = (new TaxonomiesTaxonomy)->handle('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('foo')->data(['foo' => 'bar']);

        $return = $term->save();

        $this->assertTrue($return);

        Event::assertDispatched(TermCreating::class, function ($event) use ($term) {
            return $event->term === $term;
        });

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

    #[Test]
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

    #[Test]
    public function it_saves_quietly()
    {
        Event::fake();

        $taxonomy = (new TaxonomiesTaxonomy)->handle('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('foo')->data(['foo' => 'bar']);

        $return = $term->saveQuietly();

        $this->assertTrue($return);

        Event::assertNotDispatched(TermCreating::class);
        Event::assertNotDispatched(TermSaving::class);
        Event::assertNotDispatched(TermSaved::class);
        Event::assertNotDispatched(TermCreated::class);
    }

    #[Test]
    public function if_creating_event_returns_false_the_term_doesnt_save()
    {
        Event::fake([TermCreated::class]);

        Event::listen(TermCreating::class, function () {
            return false;
        });

        $taxonomy = (new TaxonomiesTaxonomy)->handle('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('foo')->data(['foo' => 'bar']);

        $return = $term->save();

        $this->assertFalse($return);

        Event::assertNotDispatched(TermCreated::class);
    }

    #[Test]
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

    #[Test]
    public function it_gets_preview_targets()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.de/'],
        ]);

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

    #[Test]
    public function it_has_a_dirty_state()
    {
        tap(Taxonomy::make('tags')->sites(['en', 'fr']))->save();

        $term = (new Term)
            ->taxonomy('tags')
            ->slug('test');

        $term->data([
            'title' => 'English',
            'food' => 'Burger',
            'drink' => 'Water',
        ])->save();

        $this->assertFalse($term->isDirty());
        $this->assertFalse($term->isDirty('title'));
        $this->assertFalse($term->isDirty('food'));
        $this->assertFalse($term->isDirty(['title']));
        $this->assertFalse($term->isDirty(['food']));
        $this->assertFalse($term->isDirty(['title', 'food']));
        $this->assertTrue($term->isClean());
        $this->assertTrue($term->isClean('title'));
        $this->assertTrue($term->isClean('food'));
        $this->assertTrue($term->isClean(['title']));
        $this->assertTrue($term->isClean(['food']));
        $this->assertTrue($term->isClean(['title', 'food']));

        $term->merge(['title' => 'French']);

        $this->assertTrue($term->isDirty());
        $this->assertTrue($term->isDirty('title'));
        $this->assertFalse($term->isDirty('food'));
        $this->assertTrue($term->isDirty(['title']));
        $this->assertFalse($term->isDirty(['food']));
        $this->assertTrue($term->isDirty(['title', 'food']));
        $this->assertFalse($term->isClean());
        $this->assertFalse($term->isClean('title'));
        $this->assertTrue($term->isClean('food'));
        $this->assertFalse($term->isClean(['title']));
        $this->assertTrue($term->isClean(['food']));
        $this->assertFalse($term->isClean(['title', 'food']));
    }

    #[Test]
    public function it_syncs_original_at_the_right_time()
    {
        $eventsHandled = 0;

        Event::listen(function (TermCreating $event) use (&$eventsHandled) {
            $eventsHandled++;
            $this->assertTrue($event->term->isDirty());
        });
        Event::listen(function (TermSaving $event) use (&$eventsHandled) {
            $eventsHandled++;
            $this->assertTrue($event->term->isDirty());
        });
        Event::listen(function (TermCreated $event) use (&$eventsHandled) {
            $eventsHandled++;
            $this->assertTrue($event->term->isDirty());
        });
        Event::listen(function (TermSaved $event) use (&$eventsHandled) {
            $eventsHandled++;
            $this->assertTrue($event->term->isDirty());
        });

        tap(Taxonomy::make('tags'))->save();

        $term = (new Term)->taxonomy('tags')->slug('test');
        $term->dataForLocale('en', ['title' => 'The title']);
        $term->save();

        $this->assertFalse($term->isDirty());
        $this->assertEquals(4, $eventsHandled);
    }

    #[Test]
    public function it_gets_and_sets_the_layout()
    {
        $taxonomy = tap(Taxonomy::make('tags'))->save();
        $term = (new Term)->taxonomy('tags');

        // defaults to layout
        $this->assertEquals('layout', $term->layout());

        // taxonomy level overrides the default
        $taxonomy->layout('foo');
        $this->assertEquals('foo', $term->layout());

        // term level overrides the origin
        $return = $term->layout('baz');
        $this->assertEquals($term, $return);
        $this->assertEquals('baz', $term->layout());
    }

    #[Test]
    public function it_gets_and_sets_the_template()
    {
        $taxonomy = tap(Taxonomy::make('tags'))->save();
        $term = (new Term)->taxonomy('tags');

        // defaults to taxonomy.show
        $this->assertEquals('tags.show', $term->template());

        // taxonomy level overrides the default
        $taxonomy->termTemplate('foo');
        $this->assertEquals('foo', $term->template());

        // term level overrides the origin
        $return = $term->template('baz');
        $this->assertEquals($term, $return);
        $this->assertEquals('baz', $term->template());
    }

    #[Test]
    public function it_fires_a_deleting_event()
    {
        Event::fake();

        $taxonomy = tap(Taxonomy::make('tags'))->save();
        $term = (new Term)->taxonomy('tags');

        $term->delete();

        Event::assertDispatched(TermDeleting::class, function ($event) use ($term) {
            return $event->term === $term;
        });
    }

    #[Test]
    public function it_does_not_delete_when_a_deleting_event_returns_false()
    {
        Facades\Term::spy();
        Event::fake([TermDeleted::class]);

        Event::listen(TermDeleting::class, function () {
            return false;
        });

        $taxonomy = tap(Taxonomy::make('tags'))->save();
        $term = (new Term)->taxonomy('tags');

        $return = $term->delete();

        $this->assertFalse($return);
        Facades\Term::shouldNotHaveReceived('delete');
        Event::assertNotDispatched(TermDeleted::class);
    }

    #[Test]
    public function it_deletes_quietly()
    {
        Event::fake();

        $taxonomy = tap(Taxonomy::make('tags'))->save();
        $term = (new Term)->taxonomy('tags');

        $return = $term->deleteQuietly();

        Event::assertNotDispatched(TermDeleting::class);
        Event::assertNotDispatched(TermDeleted::class);

        $this->assertTrue($return);
    }

    #[Test]
    public function it_clones_internal_collections()
    {
        $taxonomy = (new TaxonomiesTaxonomy)->handle('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('foo')->data(['foo' => 'bar'])->inDefaultLocale();

        $term->set('foo', 'A');
        $term->setSupplement('bar', 'A');

        $clone = clone $term;
        $clone->set('foo', 'B');
        $clone->setSupplement('bar', 'B');

        $this->assertEquals('A', $term->get('foo'));
        $this->assertEquals('B', $clone->get('foo'));

        $this->assertEquals('A', $term->getSupplement('bar'));
        $this->assertEquals('B', $clone->getSupplement('bar'));
    }
}
