<?php

namespace Tests\Feature\Blueprints;

use Mockery;
use Statamic\Facades;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Fields\Blueprint;
use Statamic\Data\Entries\Collection;
use Tests\Fakes\FakeBlueprintRepository;
use Facades\Statamic\Fields\BlueprintRepository;

class UpdateBlueprintTest extends TestCase
{
    use FakesRoles;

    protected function setUp(): void
    {
        parent::setUp();

        BlueprintRepository::swap(new FakeBlueprintRepository);
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = Facades\User::make()->assignRole('test');
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
    function blueprint_gets_saved()
    {
        $user = Facades\User::make()->makeSuper();
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
                                ],
                                'config_overrides' => ['foo']
                            ],
                            [
                                '_id' => 'id-s1-f1',
                                'handle' => 'one-two',
                                'type' => 'inline',
                                'config' => [
                                    'type' => 'text',
                                    'foo' => 'bar',
                                ]
                            ]
                        ]
                    ]
                ]
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
                            ]
                        ],
                        [
                            'handle' => 'one-two',
                            'field' => [
                                'type' => 'text',
                                'foo' => 'bar',
                            ]
                        ]
                    ]
                ]
            ]
        ], Facades\Blueprint::find('test')->contents());
    }

    /** @test */
    function title_is_required()
    {
        $user = Facades\User::make()->makeSuper();
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
    function sections_are_required()
    {
        $user = Facades\User::make()->makeSuper();
        $this->assertCount(0, Facades\Blueprint::all());
        $blueprint = (new Blueprint)->setHandle('test')->setContents($originalContents = [
            'title' => 'Test',
            'sections' => ['foo' => 'bar']
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
    function sections_must_be_an_array()
    {
        $user = Facades\User::make()->makeSuper();
        $this->assertCount(0, Facades\Blueprint::all());
        $blueprint = (new Blueprint)->setHandle('test')->setContents($originalContents = [
            'title' => 'Test',
            'sections' => ['foo' => 'bar']
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
    function config_override_only_gets_saved_if_its_specified()
    {
        $this->markTestIncomplete();
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
