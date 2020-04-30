<?php

namespace Tests\Feature\Blueprints;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades;
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

        BlueprintRepository::swap(new FakeBlueprintRepository);
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($blueprint)
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $blueprint = Facades\Blueprint::find('test');
        $this->assertEquals('Test', $blueprint->title());
    }

    /** @test */
    public function blueprint_gets_saved()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->submit($blueprint, [
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
            ->assertStatus(204);

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
        ], Facades\Blueprint::find('test')->contents());
    }

    /** @test */
    public function title_is_required()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Blueprint::all());
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($blueprint, ['title' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertEquals('Test', Facades\Blueprint::find('test')->title());
    }

    /** @test */
    public function sections_are_required()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Blueprint::all());
        $blueprint = (new Blueprint)->setHandle('test')->setContents($originalContents = [
            'title' => 'Test',
            'sections' => ['foo' => 'bar'],
        ])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($blueprint, ['sections' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('sections');

        $this->assertEquals($originalContents, Facades\Blueprint::find('test')->contents());
    }

    /** @test */
    public function sections_must_be_an_array()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Blueprint::all());
        $blueprint = (new Blueprint)->setHandle('test')->setContents($originalContents = [
            'title' => 'Test',
            'sections' => ['foo' => 'bar'],
        ])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($blueprint, ['sections' => 'string'])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('sections');

        $this->assertEquals($originalContents, Facades\Blueprint::find('test')->contents());
    }

    /** @test */
    public function width_of_100_gets_stripped_out_for_inline_fields_but_left_in_for_reference_fields_with_config_overrides()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->submit($blueprint, [
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
            ->assertStatus(204);

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
        ], Facades\Blueprint::find('test')->contents());
    }

    private function submit($blueprint, $params = [])
    {
        return $this->patch(
            cp_route('blueprints.update', $blueprint->handle()),
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
