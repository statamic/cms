<?php

namespace Tests\Data\Taxonomies;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $this->withStandardFakeViews();
    }

    #[Test]
    public function disabled_routes_404_on_taxonomy_and_term_urls()
    {
        tap(Taxonomy::make('tags')->title('Tags')->routes(false))->save();
        tap(Term::make('test')->taxonomy('tags')->data(['title' => 'Test']))->save();

        Collection::make('blog')->taxonomies(['tags'])->save();

        $this->viewShouldReturnRaw('tags.index', 'index');
        $this->viewShouldReturnRaw('tags.show', 'show');
        $this->viewShouldReturnRaw('blog.tags.index', 'blog index');
        $this->viewShouldReturnRaw('blog.tags.show', 'blog show');

        $this->get('/tags')->assertNotFound();
        $this->get('/tags/test')->assertNotFound();
        $this->get('/blog/tags')->assertNotFound();
        $this->get('/blog/tags/test')->assertNotFound();

        $this->assertNull(Taxonomy::findByUri('/tags'));
        $this->assertNull(Term::findByUri('/tags/test'));
    }

    #[Test]
    public function custom_routes_are_used_and_old_urls_404()
    {
        tap(Taxonomy::make('tags')->title('Tags')->routes('/topics/{slug}'))->save();
        tap(Term::make('test')->taxonomy('tags')->data(['title' => 'Test']))->save();

        $this->viewShouldReturnRaw('tags.index', '{{ title }} index');
        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');

        $this->get('/topics')->assertOk()->assertSee('Tags index');
        $this->get('/topics/test')->assertOk()->assertSee('showing Test');
        $this->get('/tags')->assertNotFound();
        $this->get('/tags/test')->assertNotFound();

        $this->assertEquals('tags', Taxonomy::findByUri('/topics')->handle());
        $this->assertEquals('test', Term::findByUri('/topics/test')->slug());
        $this->assertNull(Taxonomy::findByUri('/tags'));
        $this->assertNull(Term::findByUri('/tags/test'));
    }

    #[Test]
    public function localized_custom_routes_are_used()
    {
        tap(Taxonomy::make('tags')->title('Tags')->sites(['en', 'fr'])->routes([
            'en' => '/topics/{slug}',
            'fr' => '/sujets/{slug}',
        ]))->save();

        tap(Term::make('test')->taxonomy('tags'), function ($term) {
            $term->in('en')->slug('test')->set('title', 'Test');
            $term->in('fr')->slug('le-test')->set('title', 'Le Test');
        })->save();

        $this->viewShouldReturnRaw('tags.index', '{{ title }} index');
        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');

        $this->get('/topics')->assertOk()->assertSee('Tags index');
        $this->get('/topics/test')->assertOk()->assertSee('showing Test');
        $this->get('/fr/sujets')->assertOk()->assertSee('Tags index');
        $this->get('/fr/sujets/le-test')->assertOk()->assertSee('showing Le Test');
        $this->get('/fr/topics')->assertNotFound();
        $this->get('/sujets')->assertNotFound();
    }

    #[Test]
    public function automagic_routes_still_get_collection_scoped_urls()
    {
        tap(Taxonomy::make('tags')->title('Tags'))->save();
        tap(Term::make('test')->taxonomy('tags')->data(['title' => 'Test']))->save();

        Collection::make('pages')->routes('{slug}')->save();
        $blog = EntryFactory::collection('pages')->slug('the-blog')->create();
        tap(Collection::make('blog')->taxonomies(['tags'])->mount($blog->id()))->save();

        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');
        $this->viewShouldReturnRaw('blog.tags.show', 'showing {{ title }}');
        $this->viewShouldReturnRaw('tags.index', '{{ title }} index');
        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');

        $this->get('/tags')->assertOk()->assertSee('Tags index');
        $this->get('/tags/test')->assertOk()->assertSee('showing Test');
        $this->get('/the-blog/tags')->assertOk()->assertSee('Tags index');
        $this->get('/the-blog/tags/test')->assertOk()->assertSee('showing Test');
    }

    #[Test]
    public function custom_routes_do_not_get_collection_scoped_urls()
    {
        tap(Taxonomy::make('tags')->title('Tags')->routes('/topics/{slug}'))->save();
        tap(Term::make('test')->taxonomy('tags')->data(['title' => 'Test']))->save();

        Collection::make('pages')->routes('{slug}')->save();
        $blog = EntryFactory::collection('pages')->slug('the-blog')->create();
        tap(Collection::make('blog')->taxonomies(['tags'])->mount($blog->id()))->save();

        $this->viewShouldReturnRaw('tags.index', '{{ title }} index');
        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');
        $this->viewShouldReturnRaw('blog.tags.index', 'blog index');
        $this->viewShouldReturnRaw('blog.tags.show', 'blog show');

        $this->get('/topics')->assertOk()->assertSee('Tags index');
        $this->get('/topics/test')->assertOk()->assertSee('showing Test');
        $this->get('/the-blog/topics')->assertNotFound();
        $this->get('/the-blog/topics/test')->assertNotFound();
        $this->get('/the-blog/tags')->assertNotFound();
        $this->get('/the-blog/tags/test')->assertNotFound();
    }

    #[Test]
    public function sites_missing_from_a_routes_array_get_automagic_and_collection_scoped_urls()
    {
        tap(Taxonomy::make('tags')->title('Tags')->sites(['en', 'fr'])->routes(['en' => '/topics/{slug}']))->save();

        tap(Term::make('test')->taxonomy('tags'), function ($term) {
            $term->in('en')->slug('test')->set('title', 'Test');
            $term->in('fr')->slug('le-test')->set('title', 'Le Test');
        })->save();

        Collection::make('pages')->sites(['en', 'fr'])->routes('{slug}')->save();
        EntryFactory::collection('pages')->id('blog-page')->slug('the-blog')->locale('en')->create();
        EntryFactory::collection('pages')->id('blog-page-fr')->slug('le-blog')->locale('fr')->origin('blog-page')->create();
        tap(Collection::make('blog')->sites(['en', 'fr'])->taxonomies(['tags'])->mount('blog-page'))->save();

        $this->viewShouldReturnRaw('tags.index', '{{ title }} index');
        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');
        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} blog index');
        $this->viewShouldReturnRaw('blog.tags.show', 'blog showing {{ title }}');

        // The en site opted into a custom route, so it doesn't get collection scoped urls.
        $this->get('/topics')->assertOk()->assertSee('Tags index');
        $this->get('/topics/test')->assertOk()->assertSee('showing Test');
        $this->get('/the-blog/topics/test')->assertNotFound();
        $this->get('/the-blog/tags/test')->assertNotFound();

        // The fr site didn't, so it behaves exactly like an automagic route.
        $this->get('/fr/tags')->assertOk()->assertSee('Tags index');
        $this->get('/fr/tags/le-test')->assertOk()->assertSee('showing Le Test');
        $this->get('/fr/le-blog/tags')->assertOk()->assertSee('Tags blog index');
        $this->get('/fr/le-blog/tags/le-test')->assertOk()->assertSee('blog showing Le Test');
        $this->get('/fr/topics/le-test')->assertNotFound();
    }

    #[Test]
    public function sites_missing_from_a_routes_array_are_found_by_their_collection_scoped_uri()
    {
        tap(Taxonomy::make('tags')->title('Tags')->sites(['en', 'fr'])->routes(['en' => '/topics/{slug}']))->save();

        tap(Term::make('test')->taxonomy('tags'), function ($term) {
            $term->in('en')->slug('test')->set('title', 'Test');
            $term->in('fr')->slug('le-test')->set('title', 'Le Test');
        })->save();

        Collection::make('pages')->sites(['en', 'fr'])->routes('{slug}')->save();
        EntryFactory::collection('pages')->id('blog-page')->slug('the-blog')->locale('en')->create();
        EntryFactory::collection('pages')->id('blog-page-fr')->slug('le-blog')->locale('fr')->origin('blog-page')->create();
        tap(Collection::make('blog')->sites(['en', 'fr'])->taxonomies(['tags'])->mount('blog-page'))->save();

        $this->assertNull(Taxonomy::findByUri('/the-blog/topics', 'en'));
        $this->assertNull(Term::findByUri('/the-blog/topics/test', 'en'));

        $this->assertEquals('tags', Taxonomy::findByUri('/le-blog/tags', 'fr')->handle());
        $this->assertEquals('le-test', Term::findByUri('/le-blog/tags/le-test', 'fr')->slug());
    }

    #[Test]
    public function nestable_custom_routes_use_parent_uri_and_redirect_from_flat_urls()
    {
        tap(Taxonomy::make('categories')->title('Categories')->structureContents([])->routes('/topics/{parent_uri}/{slug}'))->save();

        foreach (['animals', 'cat', 'calico'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
        ])->save();

        $this->viewShouldReturnRaw('categories.show', 'showing {{ title }}');

        $this->get('/topics/animals/cat/calico')->assertOk()->assertSeeText('showing Calico');
        $this->get('/topics/animals')->assertOk()->assertSeeText('showing Animals');
        $this->get('/topics/calico')
            ->assertStatus(301)
            ->assertRedirect('/topics/animals/cat/calico');
        $this->get('/categories/animals/cat/calico')->assertNotFound();
    }

    #[Test]
    public function whitespace_around_a_route_placeholder_is_ignored()
    {
        tap(Taxonomy::make('tags')->title('Tags')->routes('/topics/{ slug }'))->save();
        tap(Term::make('test')->taxonomy('tags')->data(['title' => 'Test']))->save();

        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');

        $this->assertEquals('/topics/test', Term::find('tags::test')->uri());
        $this->get('/topics/test')->assertOk()->assertSee('showing Test');
    }

    #[Test]
    #[DataProvider('unmatchableRouteProvider')]
    public function an_unmatchable_route_404s_without_breaking_the_rest_of_the_site($route)
    {
        tap(Taxonomy::make('tags')->title('Tags')->routes($route))->save();
        tap(Term::make('test')->taxonomy('tags')->data(['title' => 'Test']))->save();

        Collection::make('pages')->routes('{slug}')->save();
        EntryFactory::collection('pages')->slug('about')->data(['title' => 'About'])->create();

        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');
        $this->viewShouldReturnRaw('default', 'entry {{ title }}');

        $this->get('/topics/test')->assertNotFound();

        // Every non-entry url is matched against every taxonomy's route, so a
        // route that can't be compiled must not take the whole site down.
        $this->get('/about')->assertOk()->assertSee('entry About');
        $this->get('/not-a-real-url')->assertNotFound();
    }

    public static function unmatchableRouteProvider()
    {
        return [
            'antlers' => ['/topics/{{ slug }}'],
            'antlers conditional' => ['/topics/{{ if depth > 1 }}{{ parent_uri }}/{{ slug }}{{ else }}{{ slug }}{{ /if }}'],
            'duplicate placeholder' => ['/topics/{slug}/{slug}'],
            'placeholder starting with a digit' => ['/topics/{1x}/{slug}'],
        ];
    }
}
