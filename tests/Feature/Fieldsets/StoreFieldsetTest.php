<?php

namespace Tests\Feature\Fieldsets;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Fields\Fieldset;
use Statamic\Data\Entries\Collection;
use Tests\Fakes\FakeFieldsetRepository;
use Facades\Statamic\Fields\FieldsetRepository;

class StoreFieldsetTest extends TestCase
{
    use FakesRoles;

    protected function setUp(): void
    {
        parent::setUp();

        FieldsetRepository::swap(new FakeFieldsetRepository);
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::make()->assignRole('test');

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('fieldsets.store'), [
                'handle' => 'Test',
                'title' => 'Updated',
                'fields' => []
            ])
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertNull(API\Fieldset::find('test'));
    }

    /** @test */
    function fieldset_gets_created()
    {
        $user = API\User::make()->makeSuper();
        $this->assertCount(0, API\Fieldset::all());

        $this
            ->actingAs($user)
            ->post(cp_route('fieldsets.store'), [
                'handle' => 'test',
                'title' => 'Test',
                'fields' => [
                    [
                        '_id' => 'id-one',
                        'handle' => 'one',
                        'type' => 'textarea',
                        'display' => 'First Field',
                        'instructions' => 'First field instructions',
                        'foo' => 'bar'
                    ],
                    [
                        '_id' => 'id-two',
                        'handle' => 'two',
                        'type' => 'text',
                        'display' => 'Second Field',
                        'instructions' => 'Second field instructions',
                        'baz' => 'qux'
                    ],
                ]
            ])
            ->assertStatus(200)
            ->assertJson([
                'redirect' => cp_route('fieldsets.edit', 'test')
            ])
            ->assertSessionHas('message', __('Saved'));

        $this->assertCount(1, API\Fieldset::all());
        $this->assertEquals([
            'title' => 'Test',
            'fields' => [
                'one' => [
                    'type' => 'textarea',
                    'display' => 'First Field',
                    'instructions' => 'First field instructions',
                    'foo' => 'bar'
                ],
                'two' => [
                    'type' => 'text',
                    'display' => 'Second Field',
                    'instructions' => 'Second field instructions',
                    'baz' => 'qux'
                ]
            ]
        ], API\Fieldset::find('test')->contents());
    }

    /** @test */
    function handle_is_required()
    {
        $user = API\User::make()->makeSuper();
        $this->assertCount(0, API\Fieldset::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('fieldsets.store'), $this->validParams(['handle' => '']))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('handle');
    }

    /** @test */
    function title_is_required()
    {
        $user = API\User::make()->makeSuper();
        $this->assertCount(0, API\Fieldset::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('fieldsets.store'), $this->validParams(['title' => '']))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');
    }

    /** @test */
    function fields_are_required()
    {
        $user = API\User::make()->makeSuper();
        $this->assertCount(0, API\Fieldset::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('fieldsets.store'), $this->validParams(['fields' => '']))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('fields');
    }

    /** @test */
    function fields_must_be_an_array()
    {
        $user = API\User::make()->makeSuper();
        $this->assertCount(0, API\Fieldset::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('fieldsets.store'), $this->validParams(['fields' => 'string']))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('fields');
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'handle' => 'test',
            'title' => 'Test',
            'fields' => [],
        ], $overrides);
    }
}
