<?php

namespace Tests\Data\Taxonomies;

use Facades\Tests\Factories\EntryFactory;
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
        tap(Taxonomy::make('tags')->title('Tags')->routes('/topics'))->save();
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
            'en' => '/topics',
            'fr' => '/sujets',
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
    public function collection_scoped_urls_follow_the_taxonomy_route_setting()
    {
        tap(Taxonomy::make('tags')->title('Tags')->routes('/topics'))->save();
        tap(Term::make('test')->taxonomy('tags')->data(['title' => 'Test']))->save();

        Collection::make('pages')->routes('{slug}')->save();
        $blog = EntryFactory::collection('pages')->slug('the-blog')->create();
        tap(Collection::make('blog')->taxonomies(['tags'])->mount($blog->id()))->save();

        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');
        $this->viewShouldReturnRaw('blog.tags.show', 'showing {{ title }}');

        $this->get('/the-blog/topics')->assertOk()->assertSee('Tags index');
        $this->get('/the-blog/topics/test')->assertOk()->assertSee('showing Test');
        $this->get('/the-blog/tags')->assertNotFound();
        $this->get('/the-blog/tags/test')->assertNotFound();
    }

    #[Test]
    public function hierarchical_custom_routes_use_parent_uri_and_redirect_from_flat_urls()
    {
        tap(Taxonomy::make('categories')->title('Categories')->structureContents([])->routes('/topics'))->save();

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
        $this->get('/topics/calico')
            ->assertStatus(301)
            ->assertRedirect('/topics/animals/cat/calico');
        $this->get('/categories/animals/cat/calico')->assertNotFound();
    }
}
