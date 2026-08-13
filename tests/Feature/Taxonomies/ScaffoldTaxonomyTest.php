<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ScaffoldTaxonomyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_scaffold_page()
    {
        tap(Taxonomy::make('categories')->title('Categories'))->save();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('taxonomies.scaffold', 'categories'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('taxonomies/Scaffold')
                ->where('taxonomy.handle', 'categories')
            );
    }
}
