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

class ViewsTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    private $blogEntry;
    private $frenchBlogEntry;
    private $germanBlogEntry;
    private $blogCollection;
    private $tagsTaxonomy;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'de' => ['url' => '/de/', 'locale' => 'de'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $this->withStandardFakeViews();

        Collection::make('pages')->routes('{slug}')->sites(['en', 'de', 'fr'])->save();
        $this->blogEntry = EntryFactory::collection('pages')->locale('en')->slug('the-blog')->create();
        $this->frenchBlogEntry = EntryFactory::collection('pages')->locale('fr')->slug('le-blog')->origin($this->blogEntry->id())->create();
        $this->germanBlogEntry = EntryFactory::collection('pages')->locale('de')->slug('der-blog')->origin($this->blogEntry->id())->create();

        $this->blogCollection = tap(Collection::make('blog')->sites(['en', 'de', 'fr'])->taxonomies(['tags']))->save();

        $this->tagsTaxonomy = tap(Taxonomy::make('tags')->sites(['en', 'fr'])->title('Tags'))->save();

        tap(Term::make('test')->taxonomy('tags'), function ($term) {
            $term->in('en')->slug('test')->set('title', 'Test');
            $term->in('fr')->slug('le-test')->set('title', 'Le Test');
        })->save();
    }

    #[Test]
    public function the_taxonomy_url_404s_for_unconfigured_sites()
    {
        $this->viewShouldReturnRaw('tags.index', '{{ title }} index');

        $this->get('/tags')->assertOk()->assertSee('Tags index');
        $this->get('/fr/tags')->assertOk()->assertSee('Tags index');
        $this->get('/de/tags')->assertNotFound();
    }

    #[Test]
    public function the_taxonomy_url_404s_if_the_view_doesnt_exist()
    {
        $this->get('/tags')->assertNotFound();
    }

    #[Test]
    public function it_loads_the_taxonomy_url_if_the_view_exists()
    {
        $this->viewShouldReturnRaw('tags.index', '{{ title }} index');

        $this->get('/tags')->assertOk()->assertSee('Tags index');
    }

    #[Test]
    public function the_term_url_404s_if_the_view_doesnt_exist()
    {
        $this->get('/tags/test')->assertNotFound();
    }

    #[Test]
    public function it_loads_the_term_url_if_the_view_exists()
    {
        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');

        $this->get('/tags/test')->assertOk()->assertSeeText('showing Test');
    }

    #[Test]
    public function it_loads_the_localized_term_url_if_the_view_exists()
    {
        $this->viewShouldReturnRaw('tags.show', 'showing {{ title }}');

        $this->get('/fr/tags/le-test')->assertOk()->assertSee('showing Le Test');
    }

    #[Test]
    public function the_collection_specific_taxonomy_url_404s_for_unconfigured_sites()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');

        $this->get('/the-blog/tags')->assertOk()->assertSee('Tags index');
        $this->get('/fr/le-blog/tags')->assertOk()->assertSee('Tags index');
        $this->get('/de/der-blog/tags')->assertNotFound();
    }

    #[Test]
    public function the_collection_specific_taxonomy_url_404s_when_collection_is_not_configured_for_that_site()
    {
        $this->mountBlogPageToBlogCollection();

        // Set all the slugs to match the taxonomy, to make sure that the
        // missing localized collection URL isn't the thing causing the 404.
        $this->blogEntry->in('en')->slug('blog')->save();
        $this->blogEntry->in('fr')->slug('blog')->save();
        $this->blogEntry->in('de')->slug('blog')->save();
        $this->get('/blog')->assertOk();
        $this->get('/fr/blog')->assertOk();
        $this->get('/de/blog')->assertOk();

        $this->tagsTaxonomy->sites(['en', 'fr', 'de'])->save();
        $this->blogCollection->sites(['en', 'de'])->save();

        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');

        $this->get('/blog/tags')->assertOk()->assertSee('Tags index');
        $this->get('/fr/blog/tags')->assertNotFound();
        $this->get('/de/blog/tags')->assertOk()->assertSee('Tags index');
    }

    #[Test]
    public function the_collection_specific_taxonomy_url_404s_if_the_view_doesnt_exist()
    {
        $this->mountBlogPageToBlogCollection();

        $this->get('/the-blog/tags/test')->assertNotFound();
    }

    #[Test]
    public function the_collection_specific_taxonomy_url_404s_if_the_collection_is_not_configured()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');

        $this->blogCollection->taxonomies([])->save();

        $this->get('/the-blog/tags')->assertNotFound();
    }

    #[Test]
    public function it_loads_the_collection_specific_taxonomy_url_if_the_view_exists()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');

        $this->get('/the-blog/tags')->assertOk()->assertSee('Tags index');
    }

    #[Test]
    public function the_collection_specific_term_url_404s_if_the_view_doesnt_exist()
    {
        $this->mountBlogPageToBlogCollection();

        $this->get('/the-blog/tags/test')->assertNotFound();
    }

    #[Test]
    public function the_collection_specific_term_url_404s_if_the_collection_is_not_assigned_to_the_taxonomy()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.show', 'showing {{ title }}');

        $this->blogCollection->taxonomies([])->save();

        $this->get('/the-blog/tags/test')->assertNotFound();
    }

    #[Test]
    public function it_loads_the_collection_specific_term_url_if_the_view_exists()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.show', 'showing {{ title }}');

        $this->get('/the-blog/tags/test')->assertOk()->assertSee('showing Test');
    }

    #[Test]
    public function it_loads_the_localized_collection_specific_taxonomy_url_if_the_view_exists()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');

        $this->get('/fr/le-blog/tags')->assertOk()->assertSee('Tags index');
    }

    #[Test]
    public function it_loads_the_localized_collection_specific_term_url_if_the_view_exists()
    {
        $this->mountBlogPageToBlogCollection();

        $this->viewShouldReturnRaw('blog.tags.show', 'showing {{ title }}');

        $this->get('/fr/le-blog/tags/le-test')->assertOk()->assertSee('showing Le Test');
    }

    #[Test]
    public function the_unmounted_collection_specific_taxonomy_url_404s_if_the_view_doesnt_exist()
    {
        $this->get('/blog/tags/test')->assertNotFound();
    }

    #[Test]
    public function it_loads_the_unmounted_collection_specific_taxonomy_url_if_the_view_exists()
    {
        $this->viewShouldReturnRaw('blog.tags.index', '{{ title }} index');

        $this->get('/blog/tags')->assertOk()->assertSee('Tags index');
    }

    #[Test]
    public function the_unmounted_collection_specific_term_url_404s_if_the_view_doesnt_exist()
    {
        $this->get('/blog/tags/test')->assertNotFound();
    }

    #[Test]
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
