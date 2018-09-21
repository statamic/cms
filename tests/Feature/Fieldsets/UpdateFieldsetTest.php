<?php

namespace Tests\Feature\Fieldsets;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Fields\Fieldset;
use Statamic\Data\Entries\Collection;
use Facades\Statamic\Fields\FieldsetRepository;

class UpdateFieldsetListingTest extends TestCase
{
    use FakesRoles;

    protected function setUp()
    {
        parent::setUp();

        FieldsetRepository::swap(new FakeFieldsetRepository);
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::create('test')->get()->assignRole('test');
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->patch(cp_route('fieldsets.update', $fieldset->handle()), [
                'title' => 'Updated',
                'fields' => []
            ])
            ->assertRedirect('/original')
            ->assertSessionHasErrors();

        $fieldset = API\Fieldset::find('test');
        $this->assertEquals('Test', $fieldset->title());
    }

    /** @test */
    function fieldset_gets_saved()
    {
        $this->withoutExceptionHandling();
        $user = API\User::create('test')->get()->makeSuper();
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('fieldsets.update', $fieldset->handle()), [
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
        ], $fieldset->contents());
    }
}

class FakeFieldsetRepository
{
    protected $fieldsets = [];

    public function find(string $handle): ?Fieldset
    {
        return $this->fieldsets[$handle] ?? null;
    }

    public function save(Fieldset $fieldset)
    {
        $this->fieldsets[$fieldset->handle()] = $fieldset;
    }
}
