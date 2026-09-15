<?php

namespace Tests\Data\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class HierarchicalViewsTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withStandardFakeViews();

        tap(Taxonomy::make('categories')->title('Categories')->structureContents([]))->save();

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
    }

    #[Test]
    public function it_loads_hierarchical_terms_at_their_nested_urls()
    {
        $this->viewShouldReturnRaw('categories.show', 'showing {{ title }}');

        $this->get('/categories/animals')->assertOk()->assertSeeText('showing Animals');
        $this->get('/categories/animals/cat')->assertOk()->assertSeeText('showing Cat');
        $this->get('/categories/animals/cat/calico')->assertOk()->assertSeeText('showing Calico');
    }

    #[Test]
    public function flat_urls_301_redirect_to_the_canonical_nested_url()
    {
        $this->viewShouldReturnRaw('categories.show', 'showing {{ title }}');

        $this->get('/categories/calico')
            ->assertStatus(301)
            ->assertRedirect('/categories/animals/cat/calico');

        $this->get('/categories/cat')
            ->assertStatus(301)
            ->assertRedirect('/categories/animals/cat');
    }

    #[Test]
    public function incorrect_nested_urls_redirect_to_the_canonical_nested_url()
    {
        $this->viewShouldReturnRaw('categories.show', 'showing {{ title }}');

        $this->get('/categories/animals/calico')
            ->assertStatus(301)
            ->assertRedirect('/categories/animals/cat/calico');
    }

    #[Test]
    public function unknown_terms_404()
    {
        $this->viewShouldReturnRaw('categories.show', 'showing {{ title }}');

        $this->get('/categories/animals/nonexistent')->assertNotFound();
    }
}
