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
    public function it_can_check_if_has_field()
    {
        FieldsetRepository::shouldReceive('find')
            ->with('partial')
            ->andReturn((new Fieldset)->setContents([
                'fields' => [
                    ['handle' => 'two', 'field' => ['type' => 'text']],
                ],
            ]))
            ->once();

        $fieldset = new Fieldset;

        $fieldset->setContents([
            'fields' => [
                ['handle' => 'one', 'field' => ['type' => 'text']],
                ['import' => 'partial'],
            ],
        ]);

        $this->assertTrue($fieldset->hasField('one'));
        $this->assertTrue($fieldset->hasField('two'));
        $this->assertFalse($fieldset->hasField('three'));
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
}
