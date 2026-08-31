<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Taxonomies\EnsuresTermPaths;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TaxonomizeNestedTermsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Collection::make('blog')->taxonomies(['categories'])->save();
        tap(Taxonomy::make('categories')->structureContents([]))->save();
    }

    #[Test]
    public function saving_an_entry_with_a_nested_path_creates_terms_and_nests_them_in_the_tree()
    {
        $entry = tap(Entry::make()->collection('blog')->slug('show')->data([
            'title' => 'Show',
            'categories' => ['events > concerts'],
        ]))->save();

        $this->assertNotNull(Term::find('categories::events'));
        $this->assertNotNull(Term::find('categories::concerts'));
        $this->assertEquals('events', Term::find('categories::events')->title());
        $this->assertEquals('concerts', Term::find('categories::concerts')->title());

        $this->assertEquals([
            ['term' => 'events', 'children' => [
                ['term' => 'concerts'],
            ]],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->tree());

        $associations = Stache::store('terms')->store('categories')->index('associations')->items();

        $this->assertTrue($associations->contains(
            fn ($association) => $association['slug'] === 'concerts' && $association['entry'] === $entry->id()
        ));
        $this->assertFalse($associations->contains(
            fn ($association) => $association['slug'] === 'events-concerts'
        ));
    }

    #[Test]
    public function saving_an_entry_with_a_three_level_path_nests_the_full_chain()
    {
        tap(Entry::make()->collection('blog')->slug('show')->data([
            'title' => 'Show',
            'categories' => ['events > concerts > jazz'],
        ]))->save();

        $this->assertEquals([
            ['term' => 'events', 'children' => [
                ['term' => 'concerts', 'children' => [
                    ['term' => 'jazz'],
                ]],
            ]],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->tree());
    }

    #[Test]
    public function existing_segments_are_reused_and_not_reparented()
    {
        foreach (['animals', 'cat', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
        ])->save();

        tap(Entry::make()->collection('blog')->slug('show')->data([
            'title' => 'Show',
            'categories' => ['animals > cat', 'furniture > sofa'],
        ]))->save();

        $this->assertNotNull(Term::find('categories::sofa'));

        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture', 'children' => [
                ['term' => 'sofa'],
            ]],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->tree());
    }

    #[Test]
    public function a_flat_taxonomy_does_not_create_a_hierarchy_from_the_delimiter()
    {
        tap(Taxonomy::make('tags'))->save();
        Collection::findByHandle('blog')->taxonomies(['categories', 'tags'])->save();

        tap(Entry::make()->collection('blog')->slug('show')->data([
            'title' => 'Show',
            'tags' => ['events > concerts'],
        ]))->save();

        $this->assertNull(Term::find('tags::concerts'));
        $this->assertNull(Term::find('tags::events'));

        $associations = Stache::store('terms')->store('tags')->index('associations')->items();

        $this->assertTrue($associations->contains(
            fn ($association) => $association['slug'] === 'events-concerts'
        ));
    }

    #[Test]
    public function a_slash_is_now_an_ordinary_character_in_a_typed_term_value()
    {
        $entry = tap(Entry::make()->collection('blog')->slug('show')->data([
            'title' => 'Show',
            'categories' => ['AC/DC'],
        ]))->save();

        // A slash is no longer the delimiter, so this doesn't create a nested "ac" -> "dc" path.
        $this->assertNull(Term::find('categories::ac'));
        $this->assertNull(Term::find('categories::dc'));
        $this->assertNotNull(Term::find('categories::acdc'));

        $associations = Stache::store('terms')->store('categories')->index('associations')->items();

        $this->assertTrue($associations->contains(
            fn ($association) => $association['slug'] === 'acdc' && $association['entry'] === $entry->id()
        ));
    }

    #[Test]
    public function watcher_taxonomize_creates_nested_terms_from_modified_entries()
    {
        $entry = tap(Entry::make()->collection('blog')->slug('show')->data([
            'title' => 'Show',
        ]))->save();

        $entry->set('categories', ['plants > fern'])->save();

        $this->assertNotNull(Term::find('categories::plants'));
        $this->assertNotNull(Term::find('categories::fern'));
        $this->assertEquals([
            ['term' => 'plants', 'children' => [
                ['term' => 'fern'],
            ]],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->tree());
    }

    #[Test]
    public function a_denied_create_permission_does_not_create_partial_paths()
    {
        tap(Term::make('events')->taxonomy('categories')->data(['title' => 'Events']))->save();

        Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'events'],
        ])->save();

        $slug = (new EnsuresTermPaths)->ensure(
            Taxonomy::findByHandle('categories'),
            'events > concerts > jazz',
            'en',
            fn () => false
        );

        $this->assertNull($slug);
        $this->assertNull(Term::find('categories::concerts'));
        $this->assertNull(Term::find('categories::jazz'));
        $this->assertEquals([
            ['term' => 'events'],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->tree());
    }

    #[Test]
    public function saving_an_entry_with_a_path_deeper_than_max_depth_is_rejected()
    {
        Taxonomy::findByHandle('categories')->structureContents(['max_depth' => 2])->save();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        tap(Entry::make()->collection('blog')->slug('show')->data([
            'title' => 'Show',
            'categories' => ['events > concerts > jazz'],
        ]))->save();
    }
}
