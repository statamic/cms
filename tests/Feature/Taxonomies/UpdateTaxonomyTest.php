<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $taxonomy = tap(Taxonomy::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->update($taxonomy)
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_updates_a_taxonomy()
    {
        $taxonomy = tap(
            Taxonomy::make('test')
                ->title('Original title')
        )->save();
        $this->assertCount(1, Taxonomy::all());
        $this->assertEquals('Original title', $taxonomy->title());

        $this
            ->actingAs($this->userWithPermission())
            ->update($taxonomy, [
                'title' => 'Updated title',
            ])
            ->assertOk();

        $this->assertCount(1, Taxonomy::all());
        $this->assertEquals('Updated title', $taxonomy->title());
    }

    #[Test]
    public function it_associates_taxonomies_with_collections()
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
                'collections' => ['one', 'three'],
            ])
            ->assertOk();

        $this->assertEquals(['one', 'three'], $taxonomy->collections()->map->handle()->all());
        $this->assertTrue($collectionOne->taxonomies()->contains($taxonomy));
        $this->assertFalse($collectionTwo->taxonomies()->contains($taxonomy));
        $this->assertTrue($collectionThree->taxonomies()->contains($taxonomy));
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return tap(User::make()->assignRole('test'))->save();
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
