<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\DataProvider;
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
    #[DataProvider('taxonomyProvider')]
    public function a_stored_value_never_creates_a_hierarchy_from_the_delimiter($handle, $expectedTree)
    {
        tap(Taxonomy::make('tags'))->save();
        Collection::findByHandle('blog')->taxonomies(['categories', 'tags'])->save();

        $entry = tap(Entry::make()->collection('blog')->slug('show')->data([
            'title' => 'Show',
            $handle => ['events > concerts'],
        ]))->save();

        $this->assertNull(Term::find($handle.'::concerts'));
        $this->assertNull(Term::find($handle.'::events'));
        $this->assertEquals($expectedTree, Taxonomy::findByHandle($handle)->structure()?->tree()->tree() ?? []);

        $associations = Stache::store('terms')->store($handle)->index('associations')->items();

        $this->assertTrue($associations->contains(
            fn ($association) => $association['slug'] === 'events-concerts' && $association['entry'] === $entry->id()
        ));
    }

    public static function taxonomyProvider()
    {
        return [
            // The stub gets absorbed into the tree as a single flat branch, not as "events" > "concerts".
            'hierarchical' => ['categories', [['term' => 'events-concerts']]],
            'flat' => ['tags', []],
        ];
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
}
