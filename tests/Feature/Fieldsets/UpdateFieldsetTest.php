<?php

namespace Tests\Feature\Fieldsets;

use Mockery;
use Statamic\Facades;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Fields\Fieldset;
use Statamic\Entries\Collection;
use Tests\Fakes\FakeFieldsetRepository;
use Facades\Statamic\Fields\FieldsetRepository;

class UpdateFieldsetTest extends TestCase
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
        $user = Facades\User::make()->assignRole('test');
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($fieldset)
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $fieldset = Facades\Fieldset::find('test');
        $this->assertEquals('Test', $fieldset->title());
    }

    /** @test */
    function fieldset_gets_saved()
    {
        $this->withoutExceptionHandling();
        $user = Facades\User::make()->makeSuper();
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->submit($fieldset, [
                'title' => 'Updated title',
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
            ->assertStatus(204);

        $this->assertEquals([
            'title' => 'Updated title',
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
        ], Facades\Fieldset::find('test')->contents());
    }

    /** @test */
    function title_is_required()
    {
        $user = Facades\User::make()->makeSuper();
        $this->assertCount(0, Facades\Fieldset::all());
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($fieldset, ['title' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertEquals('Test', Facades\Fieldset::find('test')->title());
    }

    /** @test */
    function fields_are_required()
    {
        $user = Facades\User::make()->makeSuper();
        $this->assertCount(0, Facades\Fieldset::all());
        $fieldset = (new Fieldset)->setHandle('test')->setContents($originalContents = [
            'title' => 'Test',
            'fields' => ['foo' => 'bar']
        ])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($fieldset, ['fields' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('fields');

        $this->assertEquals($originalContents, Facades\Fieldset::find('test')->contents());
    }

    /** @test */
    function fields_must_be_an_array()
    {
        $user = Facades\User::make()->makeSuper();
        $this->assertCount(0, Facades\Fieldset::all());
        $fieldset = (new Fieldset)->setHandle('test')->setContents($originalContents = [
            'title' => 'Test',
            'fields' => ['foo' => 'bar']
        ])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($fieldset, ['fields' => 'string'])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('fields');

        $this->assertEquals($originalContents, Facades\Fieldset::find('test')->contents());
    }

    private function submit($fieldset, $params = [])
    {
        return $this->patch(
            cp_route('fieldsets.update', $fieldset->handle()),
            $this->validParams($params)
        );
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Updated',
            'fields' => [],
        ], $overrides);
    }
}
