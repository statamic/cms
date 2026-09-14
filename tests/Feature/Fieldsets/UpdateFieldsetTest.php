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
    public function fieldset_gets_saved_with_sections()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        (new Fieldset)->setHandle('seo_defaults')->setContents([
            'title' => 'SEO Defaults',
            'fields' => [
                ['handle' => 'canonical_url', 'field' => ['type' => 'text']],
            ],
        ])->save();

        $fieldset = (new Fieldset)->setHandle('test')->setContents([
            'title' => 'Test',
            'foo' => 'bar',
            'fields' => [
                ['handle' => 'legacy', 'field' => ['type' => 'text']],
            ],
        ])->save();

        $this
            ->actingAs($user)
            ->submit($fieldset, [
                'title' => 'Updated title',
                'sections' => [
                    [
                        '_id' => 'section-1',
                        'display' => 'SEO',
                        'instructions' => 'SEO fields',
                        'collapsible' => true,
                        'collapsed' => true,
                        'fields' => [
                            [
                                '_id' => 'section-1-field-1',
                                'handle' => 'meta_title',
                                'type' => 'inline',
                                'config' => [
                                    'type' => 'text',
                                    'display' => 'Meta title',
                                ],
                            ],
                            [
                                '_id' => 'section-1-field-2',
                                'type' => 'import',
                                'fieldset' => 'seo_defaults',
                                'prefix' => 'seo_',
                            ],
                        ],
                    ],
                ],
            ])
            ->assertStatus(204);

        $this->assertEquals([
            'title' => 'Updated title',
            'foo' => 'bar',
            'sections' => [
                [
                    'display' => 'SEO',
                    'instructions' => 'SEO fields',
                    'collapsible' => true,
                    'collapsed' => true,
                    'fields' => [
                        [
                            'handle' => 'meta_title',
                            'field' => [
                                'type' => 'text',
                                'display' => 'Meta title',
                            ],
                        ],
                        [
                            'import' => 'seo_defaults',
                            'prefix' => 'seo_',
                        ],
                    ],
                ],
            ],
        ], Facades\Fieldset::find('test')->contents());
    }

    #[Test]
    public function fieldset_sections_are_removed_when_updating_with_flat_fields()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $fieldset = (new Fieldset)->setHandle('test')->setContents([
            'title' => 'Test',
            'sections' => [
                [
                    'display' => 'SEO',
                    'fields' => [
                        ['handle' => 'legacy', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ])->save();

        $this
            ->actingAs($user)
            ->submit($fieldset, [
                'title' => 'Updated title',
                'fields' => [
                    [
                        '_id' => 'flat-1',
                        'handle' => 'meta_title',
                        'type' => 'inline',
                        'config' => [
                            'type' => 'text',
                            'display' => 'Meta title',
                        ],
                    ],
                ],
            ])
            ->assertStatus(204);

        $this->assertEquals([
            'title' => 'Updated title',
            'fields' => [
                [
                    'handle' => 'meta_title',
                    'field' => [
                        'type' => 'text',
                        'display' => 'Meta title',
                    ],
                ],
            ],
        ], Facades\Fieldset::find('test')->contents());
    }

    #[Test]
    public function import_section_behavior_is_saved_when_flattening_sections()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        (new Fieldset)->setHandle('seo_defaults')->setContents([
            'title' => 'SEO Defaults',
            'fields' => [
                ['handle' => 'canonical_url', 'field' => ['type' => 'text']],
            ],
        ])->save();

        $fieldset = (new Fieldset)->setHandle('test')->setContents([
            'title' => 'Test',
            'fields' => [],
        ])->save();

        $this
            ->actingAs($user)
            ->submit($fieldset, [
                'sections' => [
                    [
                        '_id' => 'section-1',
                        'display' => 'Main',
                        'fields' => [
                            [
                                '_id' => 'section-1-field-1',
                                'type' => 'import',
                                'fieldset' => 'seo_defaults',
                                'section_behavior' => 'flatten',
                            ],
                        ],
                    ],
                ],
            ])
            ->assertStatus(204);

        $this->assertEquals([
            'title' => 'Updated',
            'sections' => [
                [
                    'display' => 'Main',
                    'fields' => [
                        [
                            'import' => 'seo_defaults',
                            'section_behavior' => 'flatten',
                        ],
                    ],
                ],
            ],
        ], Facades\Fieldset::find('test')->contents());
    }

    #[Test]
    public function single_default_section_is_collapsed_back_to_flat_fields()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $fieldset = (new Fieldset)->setHandle('test')->setContents([
            'title' => 'Test',
            'fields' => [],
        ])->save();

        $this
            ->actingAs($user)
            ->submit($fieldset, [
                'sections' => [
                    [
                        '_id' => 'section-1',
                        'display' => 'Fields',
                        'fields' => [
                            [
                                '_id' => 'section-1-field-1',
                                'handle' => 'meta_title',
                                'type' => 'inline',
                                'config' => [
                                    'type' => 'text',
                                    'display' => 'Meta title',
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->assertStatus(204);

        $this->assertEquals([
            'title' => 'Updated',
            'fields' => [
                [
                    'handle' => 'meta_title',
                    'field' => [
                        'type' => 'text',
                        'display' => 'Meta title',
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
