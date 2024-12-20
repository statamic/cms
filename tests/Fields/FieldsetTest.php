<?php

namespace Tests\Fields;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\FieldsetCreated;
use Statamic\Events\FieldsetCreating;
use Statamic\Events\FieldsetDeleted;
use Statamic\Events\FieldsetDeleting;
use Statamic\Events\FieldsetSaved;
use Statamic\Events\FieldsetSaving;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FieldsetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $fieldsets;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldsets = FieldsetRepository::getFacadeRoot();
    }

    public function tearDown(): void
    {
        $this->fieldsets->all()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_gets_the_handle()
    {
        $fieldset = new Fieldset;
        $this->assertNull($fieldset->handle());

        $return = $fieldset->setHandle('test');

        $this->assertEquals($fieldset, $return);
        $this->assertEquals('test', $fieldset->handle());
    }

    #[Test]
    public function it_gets_contents()
    {
        $fieldset = new Fieldset;
        $this->assertEquals([], $fieldset->contents());

        $contents = [
            'fields' => [
                ['handle' => 'one', 'field' => ['type' => 'text']],
            ],
        ];

        $return = $fieldset->setContents($contents);

        $this->assertEquals($fieldset, $return);
        $this->assertEquals($contents, $fieldset->contents());
    }

    #[Test]
    #[DataProvider('titleProvider')]
    public function it_gets_the_title($handle, $title, $expectedTitle)
    {
        $fieldset = (new Fieldset)->setHandle($handle)->setContents(['title' => $title]);

        $this->assertEquals($expectedTitle, $fieldset->title());
    }

    public static function titleProvider()
    {
        return [
            'title' => ['test_fieldset', 'The Provided Title', 'The Provided Title'],
            'no title' => ['test_fieldset', null, 'Test fieldset'],
            'no title in subdirectory' => ['bar.test_fieldset', null, 'Test fieldset'],
            'namespaced with title' => ['foo::test_fieldset', 'The Provided Title', 'The Provided Title'],
            'namespaced with no title' => ['foo::test_fieldset', null, 'Test fieldset'],
            'namespaced with no title in subdirectory' => ['foo::bar.test_fieldset', null, 'Test fieldset'],
        ];
    }

    #[Test]
    public function it_gets_fields()
    {
        $fieldset = new Fieldset;

        $fieldset->setContents([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => ['type' => 'text'],
                ],
                [
                    'handle' => 'two',
                    'field' => ['type' => 'textarea'],
                ],
            ],
        ]);

        $fields = $fieldset->fields();

        $this->assertInstanceOf(Fields::class, $fields);
        $this->assertEveryItemIsInstanceOf(Field::class, $fields = $fields->all());
        $this->assertEquals(['one', 'two'], $fields->map->handle()->values()->all());
        $this->assertEquals(['text', 'textarea'], $fields->map->type()->values()->all());
    }

    #[Test]
    public function it_gets_fields_using_legacy_syntax()
    {
        $fieldset = new Fieldset;

        $fieldset->setContents([
            'fields' => [
                'one' => [
                    'type' => 'text',
                ],
                'two' => [
                    'type' => 'textarea',
                ],
            ],
        ]);

        $fields = $fieldset->fields();

        $this->assertInstanceOf(Fields::class, $fields);
        $this->assertEveryItemIsInstanceOf(Field::class, $fields = $fields->all());
        $this->assertEquals(['one', 'two'], $fields->map->handle()->values()->all());
        $this->assertEquals(['text', 'textarea'], $fields->map->type()->values()->all());
    }

    #[Test]
    public function gets_a_single_field()
    {
        $fieldset = new Fieldset;

        $fieldset->setContents([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => [
                        'type' => 'textarea',
                        'display' => 'First field',
                    ],
                ],
            ],
        ]);

        $field = $fieldset->field('one');

        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('First field', $field->display());
        $this->assertEquals('textarea', $field->type());

        $this->assertNull($fieldset->field('unknown'));
    }

    #[Test]
    public function gets_blueprints_importing_fieldset()
    {
        $fieldset = Fieldset::make('seo')->setContents(['fields' => [
            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
        ]])->save();

        tap(Collection::make('one'))->save();
        $blueprintA = Blueprint::make('one')->setNamespace('collections.one')->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'title', 'field' => ['type' => 'text']],
                                ['handle' => 'slug', 'field' => ['type' => 'slug']],
                                ['import' => 'seo'],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $importedBy = $fieldset->importedBy();

        $this->assertCount(1, $importedBy['blueprints']);
        $this->assertEquals($blueprintA->handle(), $importedBy['blueprints']->first()->handle());
    }

    #[Test]
    public function gets_blueprints_importing_fieldset_inside_grid()
    {
        $fieldset = Fieldset::make('seo')->setContents(['fields' => [
            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
        ]])->save();

        tap(Collection::make('one'))->save();
        $blueprintA = Blueprint::make('one')->setNamespace('collections.one')->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'title', 'field' => ['type' => 'text']],
                                ['handle' => 'slug', 'field' => ['type' => 'slug']],
                                [
                                    'handle' => 'grid',
                                    'field' => [
                                        'type' => 'grid',
                                        'fields' => [
                                            ['import' => 'seo'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $importedBy = $fieldset->importedBy();

        $this->assertCount(1, $importedBy['blueprints']);
        $this->assertEquals($blueprintA->handle(), $importedBy['blueprints']->first()->handle());
    }

    #[Test]
    public function gets_blueprints_importing_fieldset_inside_replicator()
    {
        $fieldset = Fieldset::make('seo')->setContents(['fields' => [
            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
        ]])->save();

        tap(Collection::make('one'))->save();
        $blueprintA = Blueprint::make('one')->setNamespace('collections.one')->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'title', 'field' => ['type' => 'text']],
                                ['handle' => 'slug', 'field' => ['type' => 'slug']],
                                [
                                    'handle' => 'replicator',
                                    'field' => [
                                        'type' => 'replicator',
                                        'sets' => [
                                            'set_group' => [
                                                'sets' => [
                                                    'set' => [
                                                        'fields' => [
                                                            ['import' => 'seo'],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $importedBy = $fieldset->importedBy();

        $this->assertCount(1, $importedBy['blueprints']);
        $this->assertEquals($blueprintA->handle(), $importedBy['blueprints']->first()->handle());
    }

    #[Test]
    public function gets_blueprints_importing_single_field_from_fieldset()
    {
        $fieldset = Fieldset::make('seo')->setContents(['fields' => [
            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
        ]])->save();

        tap(Collection::make('one'))->save();
        $blueprintA = Blueprint::make('one')->setNamespace('collections.one')->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'title', 'field' => ['type' => 'text']],
                                ['handle' => 'slug', 'field' => ['type' => 'slug']],
                                ['handle' => 'meta_title', 'field' => 'seo.meta_title'],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $importedBy = $fieldset->importedBy();

        $this->assertCount(1, $importedBy['blueprints']);
        $this->assertEquals($blueprintA->handle(), $importedBy['blueprints']->first()->handle());
    }

    #[Test]
    public function gets_fieldsets_importing_fieldset()
    {
        $fieldset = Fieldset::make('seo')->setContents(['fields' => [
            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
        ]])->save();

        $fieldsetA = Fieldset::make('one')
            ->setContents([
                'fields' => [
                    ['import' => 'seo'],
                ],
            ])
            ->save();

        $importedBy = $fieldset->importedBy();

        $this->assertCount(1, $importedBy['fieldsets']);
        $this->assertEquals($fieldsetA->handle(), $importedBy['fieldsets']->first()->handle());
    }

    #[Test]
    public function gets_fieldsets_importing_fieldset_inside_grid()
    {
        $fieldset = Fieldset::make('seo')->setContents(['fields' => [
            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
        ]])->save();

        $fieldsetA = Fieldset::make('one')
            ->setContents([
                'fields' => [
                    [
                        'handle' => 'grid',
                        'field' => [
                            'type' => 'grid',
                            'fields' => [
                                ['import' => 'seo'],
                            ],
                        ],
                    ],
                ],
            ])
            ->save();

        $importedBy = $fieldset->importedBy();

        $this->assertCount(1, $importedBy['fieldsets']);
        $this->assertEquals($fieldsetA->handle(), $importedBy['fieldsets']->first()->handle());
    }

    #[Test]
    public function gets_fieldsets_importing_fieldset_inside_replicator()
    {
        $fieldset = Fieldset::make('seo')->setContents(['fields' => [
            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
        ]])->save();

        $fieldsetA = Fieldset::make('one')
            ->setContents([
                'fields' => [
                    [
                        'handle' => 'replicator',
                        'field' => [
                            'type' => 'replicator',
                            'sets' => [
                                'set_group' => [
                                    'sets' => [
                                        'set' => [
                                            'fields' => [
                                                ['import' => 'seo'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->save();

        $importedBy = $fieldset->importedBy();

        $this->assertCount(1, $importedBy['fieldsets']);
        $this->assertEquals($fieldsetA->handle(), $importedBy['fieldsets']->first()->handle());
    }

    #[Test]
    public function gets_fieldsets_importing_single_field_from_fieldset()
    {
        $fieldset = Fieldset::make('seo')->setContents(['fields' => [
            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
        ]])->save();

        $fieldsetA = Fieldset::make('one')
            ->setContents([
                'fields' => [
                    ['handle' => 'meta_title', 'field' => 'seo.meta_title'],
                ],
            ])
            ->save();

        $importedBy = $fieldset->importedBy();

        $this->assertCount(1, $importedBy['fieldsets']);
        $this->assertEquals($fieldsetA->handle(), $importedBy['fieldsets']->first()->handle());
    }

    #[Test]
    public function it_saves_through_the_repository()
    {
        Event::fake();

        $fieldset = (new Fieldset)->setHandle('seo');

        FieldsetRepository::shouldReceive('find')->with($fieldset->handle());
        FieldsetRepository::shouldReceive('save')->with($fieldset)->once();

        $return = $fieldset->save();

        $this->assertEquals($fieldset, $return);

        Event::assertDispatched(FieldsetCreating::class, function ($event) use ($fieldset) {
            return $event->fieldset === $fieldset;
        });

        Event::assertDispatched(FieldsetSaving::class, function ($event) use ($fieldset) {
            return $event->fieldset === $fieldset;
        });

        Event::assertDispatched(FieldsetCreated::class, function ($event) use ($fieldset) {
            return $event->fieldset === $fieldset;
        });

        Event::assertDispatched(FieldsetSaved::class, function ($event) use ($fieldset) {
            return $event->fieldset === $fieldset;
        });
    }

    #[Test]
    public function it_dispatches_fieldset_created_only_once()
    {
        Event::fake();

        $fieldset = (new Fieldset)->setHandle('seo');

        FieldsetRepository::shouldReceive('save')->with($fieldset);
        FieldsetRepository::shouldReceive('find')->with($fieldset->handle())->times(3)->andReturn(null, $fieldset, $fieldset);

        $fieldset->save();
        $fieldset->save();
        $fieldset->save();

        Event::assertDispatched(FieldsetSaved::class, 3);
        Event::assertDispatched(FieldsetCreated::class, 1);
    }

    #[Test]
    public function it_saves_quietly()
    {
        Event::fake();

        $fieldset = (new Fieldset)->setHandle('seo');

        FieldsetRepository::shouldReceive('find')->with($fieldset->handle());
        FieldsetRepository::shouldReceive('save')->with($fieldset)->once();

        $return = $fieldset->saveQuietly();

        $this->assertEquals($fieldset, $return);

        Event::assertNotDispatched(FieldsetCreating::class);
        Event::assertNotDispatched(FieldsetSaving::class);
        Event::assertNotDispatched(FieldsetSaved::class);
        Event::assertNotDispatched(FieldsetCreated::class);
    }

    #[Test]
    public function if_creating_event_returns_false_the_fieldset_doesnt_save()
    {
        Event::fake([FieldsetCreated::class]);

        Event::listen(FieldsetCreating::class, function () {
            return false;
        });

        $fieldset = (new Fieldset)->setHandle('seo');

        $return = $fieldset->save();

        $this->assertFalse($return);
        Event::assertNotDispatched(FieldsetCreated::class);
    }

    #[Test]
    public function if_saving_event_returns_false_the_fieldset_doesnt_save()
    {
        Event::fake([FieldsetSaved::class]);

        Event::listen(FieldsetSaving::class, function () {
            return false;
        });

        $fieldset = (new Fieldset)->setHandle('seo');

        FieldsetRepository::shouldReceive('find')->with($fieldset->handle());
        FieldsetRepository::shouldReceive('save')->with($fieldset)->once();

        $return = $fieldset->saveQuietly();

        $this->assertEquals($fieldset, $return);

        Event::assertNotDispatched(FieldsetSaved::class);
    }

    #[Test]
    public function it_fires_a_deleting_event()
    {
        Event::fake();

        $fieldset = (new Fieldset)->setHandle('test');

        $fieldset->delete();

        Event::assertDispatched(FieldsetDeleting::class, function ($event) use ($fieldset) {
            return $event->fieldset === $fieldset;
        });
    }

    #[Test]
    public function it_does_not_delete_when_a_deleting_event_returns_false()
    {
        FieldsetRepository::spy();
        Event::fake([FieldsetDeleted::class]);

        Event::listen(FieldsetDeleting::class, function () {
            return false;
        });

        $fieldset = (new Fieldset)->setHandle('test');
        $return = $fieldset->delete();

        $this->assertFalse($return);
        FieldsetRepository::shouldNotHaveReceived('delete');
        Event::assertNotDispatched(FieldsetDeleted::class);
    }

    #[Test]
    public function it_deletes_quietly()
    {
        Event::fake();

        $fieldset = (new Fieldset)->setHandle('test');

        $return = $fieldset->deleteQuietly();

        Event::assertNotDispatched(FieldsetDeleting::class);
        Event::assertNotDispatched(FieldsetDeleted::class);

        $this->assertTrue($return);
    }

    #[Test]
    public function it_ensures_a_field_exists()
    {
        $fieldset = (new Fieldset)->setContents(['fields' => [
            ['handle' => 'existing', 'field' => ['type' => 'text']],
        ]]);

        $return = $fieldset->ensureField('new', ['type' => 'textarea']);

        $this->assertEquals($fieldset, $return);
        $this->assertTrue($fieldset->hasField('existing'));
        $this->assertTrue($fieldset->hasField('new'));

        $this->assertEquals(['fields' => [
            ['handle' => 'existing', 'field' => ['type' => 'text']],
            ['handle' => 'new', 'field' => ['type' => 'textarea']],
        ]], $fieldset->contents());

        $this->assertEquals(['type' => 'textarea'], $fieldset->fields()->get('new')->config());
    }

    #[Test]
    public function it_can_add_fields_multiple_times()
    {
        $fieldset = (new Fieldset)->setContents(['fields' => [
            ['handle' => 'existing', 'field' => ['type' => 'text']],
        ]]);

        $fieldset
            ->ensureField('new_one', ['type' => 'text'])
            ->ensureField('new_two', ['type' => 'textarea']);

        $this->assertTrue($fieldset->hasField('existing'));
        $this->assertTrue($fieldset->hasField('new_one'));
        $this->assertTrue($fieldset->hasField('new_two'));

        $this->assertEquals(['fields' => [
            ['handle' => 'existing', 'field' => ['type' => 'text']],
            ['handle' => 'new_one', 'field' => ['type' => 'text']],
            ['handle' => 'new_two', 'field' => ['type' => 'textarea']],
        ]], $fieldset->contents());

        $this->assertEquals(['type' => 'text'], $fieldset->fields()->get('new_one')->config());
        $this->assertEquals(['type' => 'textarea'], $fieldset->fields()->get('new_two')->config());
    }

    #[Test]
    public function it_ensures_a_field_has_config()
    {
        FieldsetRepository::shouldReceive('find')->with('the_partial')->andReturn(
            (new Fieldset)->setContents(['fields' => [
                [
                    'handle' => 'the_field',
                    'field' => ['type' => 'text', 'do_not_touch_other_config' => true],
                ],
            ]])
        );

        $fieldset = (new Fieldset)->setContents(['fields' => [
            ['handle' => 'title', 'field' => ['type' => 'text']],
            ['handle' => 'author', 'field' => ['type' => 'text', 'do_not_touch_other_config' => true]],
            ['handle' => 'content', 'field' => ['type' => 'text']],
            ['handle' => 'the_field', 'field' => 'the_partial.the_field', 'config' => ['type' => 'text', 'do_not_touch_other_config' => true]],
        ]]);

        $fields = $fieldset
            ->ensureFieldHasConfig('author', ['visibility' => 'read_only'])
            ->ensureFieldHasConfig('the_field', ['visibility' => 'read_only'])
            ->fields();

        $this->assertEquals(['type' => 'text'], $fields->get('title')->config());
        $this->assertEquals(['type' => 'text'], $fields->get('content')->config());

        $expectedConfig = [
            'type' => 'text',
            'do_not_touch_other_config' => true,
            'visibility' => 'read_only',
        ];

        $this->assertEquals($expectedConfig, $fields->get('author')->config());
        $this->assertEquals($expectedConfig, $fields->get('the_field')->config());
    }

    #[Test]
    public function it_merges_previously_undefined_keys_into_the_config_when_ensuring_a_field_exists_and_it_already_exists()
    {
        $fieldset = (new Fieldset)->setContents(['fields' => [
            ['handle' => 'existing', 'field' => ['type' => 'text']],
        ]]);

        $return = $fieldset->ensureField('existing', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($fieldset, $return);
        $this->assertTrue($fieldset->hasField('existing'));

        $this->assertEquals(['fields' => [
            ['handle' => 'existing', 'field' => ['type' => 'text', 'foo' => 'bar']],
        ]], $fieldset->contents());

        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $fieldset->fields()->get('existing')->config());
    }

    #[Test]
    public function it_merges_previously_undefined_keys_into_the_config_when_ensuring_prepended_a_field_exists_and_it_already_exists()
    {
        $fieldset = (new Fieldset)->setContents(['fields' => [
            ['handle' => 'first', 'field' => ['type' => 'text']],
            ['handle' => 'existing', 'field' => ['type' => 'text']],
        ]]);

        $return = $fieldset->ensureField('existing', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($fieldset, $return);
        $this->assertTrue($fieldset->hasField('existing'));

        $this->assertEquals(['fields' => [
            ['handle' => 'first', 'field' => ['type' => 'text']],
            ['handle' => 'existing', 'field' => ['type' => 'text', 'foo' => 'bar']],
        ]], $fieldset->contents());

        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $fieldset->fields()->get('existing')->config());
    }

    #[Test]
    public function it_merges_config_overrides_for_previously_undefined_keys_when_ensuring_a_field_and_it_already_exists_as_a_reference()
    {
        FieldsetRepository::shouldReceive('find')->with('the_partial')->andReturn(
            (new Fieldset)->setContents(['fields' => [
                [
                    'handle' => 'the_field',
                    'field' => ['type' => 'text'],
                ],
            ]])
        );

        $fieldset = (new Fieldset)->setContents(['fields' => [
            ['handle' => 'from_partial', 'field' => 'the_partial.the_field'],
        ]]);

        $return = $fieldset->ensureField('from_partial', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($fieldset, $return);
        $this->assertTrue($fieldset->hasField('from_partial'));

        $this->assertEquals(['fields' => [
            ['handle' => 'from_partial', 'field' => 'the_partial.the_field', 'config' => ['foo' => 'bar']],
        ]], $fieldset->contents());

        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $fieldset->fields()->get('from_partial')->config());
    }

    #[Test]
    public function it_merges_undefined_config_overrides_when_ensuring_a_field_that_already_exists_inside_an_imported_fieldset()
    {
        FieldsetRepository::shouldReceive('find')->with('the_partial')->andReturn(
            (new Fieldset)->setContents(['fields' => [
                [
                    'handle' => 'one',
                    'field' => ['type' => 'text'],
                ],
            ]])
        );

        $fieldset = (new Fieldset)->setContents(['fields' => [
            ['import' => 'the_partial'],
        ]]);

        $return = $fieldset->ensureField('one', ['type' => 'textarea', 'foo' => 'bar']);

        $this->assertEquals($fieldset, $return);
        $this->assertTrue($fieldset->hasField('one'));

        $this->assertEquals(['fields' => [
            [
                'import' => 'the_partial',
                'config' => [
                    'one' => ['foo' => 'bar'],
                ],
            ],
        ]], $fieldset->contents());

        $this->assertEquals(['type' => 'text', 'foo' => 'bar'], $fieldset->fields()->get('one')->config());
    }

    #[Test]
    public function it_ensures_a_field_exists_if_it_doesnt_and_prepends_it()
    {
        $fieldset = (new Fieldset)->setContents(['fields' => [
            ['handle' => 'one', 'field' => ['type' => 'text']],
            ['handle' => 'two', 'field' => ['type' => 'text']],
        ]]);

        $this->assertFalse($fieldset->hasField('three'));

        $return = $fieldset->ensureFieldPrepended('three', ['type' => 'textarea']); // field "three" doesnt exist, so it should get added to the start.

        $this->assertEquals($fieldset, $return);
        $this->assertTrue($fieldset->hasField('three'));

        tap($fieldset->fields()->all(), function ($items) {
            $this->assertCount(3, $items);
            $this->assertEveryItemIsInstanceOf(Field::class, $items);
            $this->assertEquals(['three', 'one', 'two'], $items->map->handle()->values()->all());
            $this->assertEquals(['textarea', 'text', 'text'], $items->map->type()->values()->all());
        });
    }
}
