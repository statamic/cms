<?php

namespace Tests\Feature\Fieldsets;

use Facades\Statamic\Fields\FieldRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldset;
use Tests\Fakes\FakeFieldsetRepository;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateFieldsetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        FieldsetRepository::swap(new FakeFieldsetRepository);
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
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

    #[Test]
    public function fieldset_gets_saved()
    {
        $this->withoutExceptionHandling();
        FieldRepository::shouldReceive('find')->with('somefieldset.somefield')->andReturn(new Field('somefield', []));
        $user = tap(Facades\User::make()->makeSuper())->save();
        $fieldset = (new Fieldset)->setHandle('test')->setContents([
            'title' => 'Test',
            'foo' => 'bar',
        ])->save();

        $this
            ->actingAs($user)
            ->submit($fieldset, [
                'title' => 'Updated title',
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
            ])
            ->assertStatus(204);

        $this->assertEquals([
            'title' => 'Updated title',
            'foo' => 'bar',
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
        ], Facades\Fieldset::find('test')->contents());
    }

    #[Test]
    public function title_is_required()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
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

    #[Test]
    public function fields_are_required()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Fieldset::all());
        $fieldset = (new Fieldset)->setHandle('test')->setContents($originalContents = [
            'title' => 'Test',
            'fields' => [
                ['handle' => 'foo', 'field' => ['type' => 'bar']],
            ],
        ])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($fieldset, ['fields' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('fields');

        $this->assertEquals($originalContents, Facades\Fieldset::find('test')->contents());
    }

    #[Test]
    public function fields_must_be_an_array()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Fieldset::all());
        $fieldset = (new Fieldset)->setHandle('test')->setContents($originalContents = [
            'title' => 'Test',
            'fields' => [
                ['handle' => 'foo', 'field' => 'bar'],
            ],
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
