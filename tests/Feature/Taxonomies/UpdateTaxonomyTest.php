<?php

namespace Tests\Feature\Taxonomies;

use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateTaxonomyTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_associates_taxonomies_with_collections()
    {
        $taxonomy = tap(Taxonomy::make('test'))->save();
        $collectionOne = tap(Collection::make('one')->taxonomies(['test']))->save();
        $collectionTwo = tap(Collection::make('two')->taxonomies(['test']))->save();
        $collectionThree = tap(Collection::make('three'))->save();

        $this->assertEquals(['one', 'two'], $taxonomy->collections()->map->handle()->all());
        $this->assertTrue($collectionOne->taxonomies()->contains($taxonomy));
        $this->assertTrue($collectionTwo->taxonomies()->contains($taxonomy));
        $this->assertFalse($collectionThree->taxonomies()->contains($taxonomy));

        $this
            ->actingAs($this->userWithPermission())
            ->update($taxonomy, [
                'collections' => ['one', 'three']
            ])
            ->assertOk();

        $this->assertEquals(['one', 'three'], $taxonomy->collections()->map->handle()->all());
        $this->assertTrue($collectionOne->taxonomies()->contains($taxonomy));
        $this->assertFalse($collectionTwo->taxonomies()->contains($taxonomy));
        $this->assertTrue($collectionThree->taxonomies()->contains($taxonomy));
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure taxonomies']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function update($taxonomy, $params = [])
    {
        $params = array_merge([
            'title' => 'Updated title',
        ], $params);

        return $this->patch(cp_route('taxonomies.update', $taxonomy->handle()), $params);
    }
}
