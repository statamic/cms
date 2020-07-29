<?php

namespace Tests\Feature\Collections\Blueprints;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Tests\Fakes\FakeBlueprintRepository;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateBlueprintTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        BlueprintRepository::swap(new FakeBlueprintRepository(BlueprintRepository::getFacadeRoot()));
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($collection, $blueprint)
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $blueprint = Facades\Blueprint::find('test');
        $this->assertEquals('Test', $blueprint->title());
    }

    /** @test */
    public function blueprint_gets_saved()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();
        $blueprint = (new Blueprint)->setNamespace('collections.test')->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->submit($collection, $blueprint, [
                'title' => 'Updated title',
                'sections' => [
                    [
                        '_id' => 'id-one',
                        'handle' => 'one',
                        'display' => 'Section One',
                        'fields' => [
                            [
                                '_id' => 'id-s1-f1',
                                'handle' => 'one-one',
                                'type' => 'reference',
                                'field_reference' => 'somefieldset.somefield',
                                'config' => [
                                    'foo' => 'bar',
                                    'baz' => 'qux', // not in config_overrides so it shouldn't get saved
                                ],
                                'config_overrides' => ['foo'],
                            ],
                            [
                                '_id' => 'id-s1-f1',
                                'handle' => 'one-two',
                                'type' => 'inline',
                                'config' => [
                                    'type' => 'text',
                                    'foo' => 'bar',
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $this->assertEquals([
            'title' => 'Updated title',
            'sections' => [
                'one' => [
                    'display' => 'Section One',
                    'fields' => [
                        [
                            'handle' => 'one-one',
                            'field' => 'somefieldset.somefield',
                            'config' => [
                                'foo' => 'bar',
                            ],
                        ],
                        [
                            'handle' => 'one-two',
                            'field' => [
                                'type' => 'text',
                                'foo' => 'bar',
                            ],
                        ],
                    ],
                ],
            ],
        ], Facades\Blueprint::find('collections.test.test')->contents());
    }

    /** @test */
    public function title_is_required()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();
        $this->assertCount(0, Facades\Blueprint::in('collections/test'));
        $blueprint = (new Blueprint)->setNamespace('collections.test')->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($collection, $blueprint, ['title' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertEquals('Test', Facades\Blueprint::find('collections.test.test')->title());
    }

    /** @test */
    public function sections_are_required()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();
        $this->assertCount(0, Facades\Blueprint::in('collections/test'));
        $blueprint = tap($collection->entryBlueprint())->save();
        $originalContents = $blueprint->contents();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($collection, $blueprint, ['sections' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('sections');

        $this->assertEquals($originalContents, Facades\Blueprint::find('collections.test.test')->contents());
    }

    /** @test */
    public function sections_must_be_an_array()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();
        $this->assertCount(0, Facades\Blueprint::in('collections/test'));
        $blueprint = tap($collection->entryBlueprint())->save();
        $originalContents = $blueprint->contents();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($collection, $blueprint, ['sections' => 'string'])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('sections');

        $this->assertEquals($originalContents, Facades\Blueprint::find('collections.test.test')->contents());
    }

    /** @test */
    public function width_of_100_gets_stripped_out_for_inline_fields_but_left_in_for_reference_fields_with_config_overrides()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();
        $blueprint = (new Blueprint)->setNamespace('collections.test')->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->submit($collection, $blueprint, [
                'title' => 'Updated title',
                'sections' => [
                    [
                        '_id' => 'id-one',
                        'handle' => 'one',
                        'display' => 'Section One',
                        'fields' => [
                            [
                                '_id' => 'id-s1-f1',
                                'handle' => 'one-one',
                                'type' => 'reference',
                                'field_reference' => 'somefieldset.somefield',
                                'config' => [
                                    'foo' => 'bar',
                                    'width' => 100,
                                ],
                                'config_overrides' => ['width'],
                            ],
                            [
                                '_id' => 'id-s1-f2',
                                'handle' => 'one-two',
                                'type' => 'inline',
                                'config' => [
                                    'type' => 'text',
                                    'width' => 100,
                                ],
                            ],
                            [
                                '_id' => 'id-s1-f3',
                                'handle' => 'one-three',
                                'type' => 'inline',
                                'config' => [
                                    'type' => 'text',
                                    'width' => 50,
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $this->assertEquals([
            'title' => 'Updated title',
            'sections' => [
                'one' => [
                    'display' => 'Section One',
                    'fields' => [
                        [
                            'handle' => 'one-one',
                            'field' => 'somefieldset.somefield',
                            'config' => [
                                'width' => 100,
                            ],
                        ],
                        [
                            'handle' => 'one-two',
                            'field' => [
                                'type' => 'text',
                            ],
                        ],
                        [
                            'handle' => 'one-three',
                            'field' => [
                                'type' => 'text',
                                'width' => 50,
                            ],
                        ],
                    ],
                ],
            ],
        ], Facades\Blueprint::find('collections.test.test')->contents());
    }

    private function submit($collection, $blueprint, $params = [])
    {
        return $this->patch(
            cp_route('collections.blueprints.update', [$collection, $blueprint]),
            $this->validParams($params)
        );
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Updated',
            'sections' => [],
        ], $overrides);
    }
}
