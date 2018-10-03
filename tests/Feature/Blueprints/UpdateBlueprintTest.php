<?php

namespace Tests\Feature\Blueprints;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Fields\Blueprint;
use Statamic\Data\Entries\Collection;
use Tests\Fakes\FakeBlueprintRepository;
use Facades\Statamic\Fields\BlueprintRepository;

class UpdateBlueprintTest extends TestCase
{
    use FakesRoles;

    protected function setUp()
    {
        parent::setUp();

        BlueprintRepository::swap(new FakeBlueprintRepository);
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::create('test')->get()->assignRole('test');
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($blueprint)
            ->assertRedirect('/original')
            ->assertSessionHasErrors();

        $blueprint = API\Blueprint::find('test');
        $this->assertEquals('Test', $blueprint->title());
    }

    /** @test */
    function blueprint_gets_saved()
    {
        $user = API\User::create('test')->get()->makeSuper();
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
                                ]
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
        ], API\Blueprint::find('test')->contents());
    }

    /** @test */
    function title_is_required()
    {
        $user = API\User::create('test')->get()->makeSuper();
        $this->assertCount(0, API\Blueprint::all());
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($blueprint, ['title' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertEquals('Test', API\Blueprint::find('test')->title());
    }

    /** @test */
    function sections_are_required()
    {
        $user = API\User::create('test')->get()->makeSuper();
        $this->assertCount(0, API\Blueprint::all());
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

        $this->assertEquals($originalContents, API\Blueprint::find('test')->contents());
    }

    /** @test */
    function sections_must_be_an_array()
    {
        $user = API\User::create('test')->get()->makeSuper();
        $this->assertCount(0, API\Blueprint::all());
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

        $this->assertEquals($originalContents, API\Blueprint::find('test')->contents());
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
