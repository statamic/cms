<?php

namespace Tests\Data\Taxonomies;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewsTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    private $blogEntry;
    private $frenchBlogEntry;
    private $blogCollection;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $this->withStandardFakeViews();

        Collection::make('pages')->routes('{slug}')->sites(['en', 'fr'])->save();
        $this->blogEntry = EntryFactory::collection('pages')->locale('en')->slug('the-blog')->create();
        $this->frenchBlogEntry = EntryFactory::collection('pages')->locale('fr')->slug('le-blog')->origin($this->blogEntry->id())->create();

        $this->blogCollection = tap(Collection::make('blog')->sites(['en', 'fr'])->taxonomies(['tags']))->save();

        Taxonomy::make('tags')->sites(['en', 'fr'])->title('Tags')->save();

        tap(Term::make('test')->taxonomy('tags'), function ($term) {
            $term->in('en')->slug('test')->set('title', 'Test');
            $term->in('fr')->slug('le-test')->set('title', 'Le Test');
        })->save();
    }

    /** @test */
    public function the_taxonomy_url_404s_if_the_view_doesnt_exist()
    {
        $this->get('/tags')->assertNotFound();
    }

    /** @test */
    public function it_loads_the_taxonomy_url_if_the_view_exists()
    {
        $this->viewShouldReturnRaw('tags.index', '{{ title }} index');

        $this->get('/tags')->assertOk()->assertSee('Tags index');
    }

    /** @test */
    public function the_term_url_404s_if_the_view_doesnt_exist()
    {
        $this->get('/tags/test')->assertNotFound();
    }

    /** @test */
    public function it_loads_the_term_url_if_the_view_exists()
    {
        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');

        $this->get('/tags/test')->assertOk()->assertSeeText('showing Test');
    }

    /** @test */
    public function it_loads_the_localized_term_url_if_the_view_exists()
    {
        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');

        $this->get('/fr/tags/le-test')->assertOk()->assertSee('showing Le Test');
    }

    /** @test */
    public function the_collection_specific_taxonomy_url_404s_if_the_view_doesnt_exist()
    {
        $this->mountBlogPageToBlogCollection();

        $this->get('/the-blog/tags/test')->assertNotFound();
    }

    /** @test */
    public function it_loads_the_collection_specific_taxonomy_url_if_the_view_exists()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');

        $this->get('/the-blog/tags')->assertOk()->assertSee('Tags index');
    }

    /** @test */
    public function the_collection_specific_term_url_404s_if_the_view_doesnt_exist()
    {
        $this->mountBlogPageToBlogCollection();

        $this->get('/the-blog/tags/test')->assertNotFound();
    }

    /** @test */
    public function it_loads_the_collection_specific_term_url_if_the_view_exists()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.show', 'showing {{ title }}');

        $this->get('/the-blog/tags/test')->assertOk()->assertSee('showing Test');
    }

    /** @test */
    public function it_loads_the_localized_collection_specific_taxonomy_url_if_the_view_exists()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');

        $this->get('/fr/le-blog/tags')->assertOk()->assertSee('Tags index');
    }

    /** @test */
    public function it_loads_the_localized_collection_specific_term_url_if_the_view_exists()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.show', 'showing {{ title }}');

        $this->get('/fr/le-blog/tags/le-test')->assertOk()->assertSee('showing Le Test');
    }

    /** @test */
    public function the_unmounted_collection_specific_taxonomy_url_404s_if_the_view_doesnt_exist()
    {
        $this->get('/blog/tags/test')->assertNotFound();
    }

    /** @test */
    public function it_loads_the_unmounted_collection_specific_taxonomy_url_if_the_view_exists()
    {
        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');

        $this->get('/blog/tags')->assertOk()->assertSee('Tags index');
    }

    /** @test */
    public function the_unmounted_collection_specific_term_url_404s_if_the_view_doesnt_exist()
    {
        $this->get('/blog/tags/test')->assertNotFound();
    }

    /** @test */
    public function it_loads_the_unmounted_collection_specific_term_url_if_the_view_exists()
    {
        $this->viewShouldReturnRaw('blog.tags.show', 'showing {{ title }}');

        $this->get('/blog/tags/test')->assertOk()->assertSee('showing Test');
    }

    private function mountBlogPageToBlogCollection()
    {
        $this->blogCollection->mount($this->blogEntry->id())->save();
    }
}
