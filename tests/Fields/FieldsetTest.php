<?php

namespace Tests\Fields;

use Illuminate\Support\Facades\Event;
use Statamic\Events\FieldsetCreated;
use Statamic\Events\FieldsetSaved;
use Statamic\Events\FieldsetSaving;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Tests\TestCase;

class FieldsetTest extends TestCase
{
    /** @test */
    public function it_gets_the_handle()
    {
        $fieldset = new Fieldset;
        $this->assertNull($fieldset->handle());

        $return = $fieldset->setHandle('test');

        $this->assertEquals($fieldset, $return);
        $this->assertEquals('test', $fieldset->handle());
    }

    /** @test */
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

    /**
     * @test
     *
     * @dataProvider titleProvider
     */
    public function it_gets_the_title($handle, $title, $expectedTitle)
    {
        $fieldset = (new Fieldset)->setHandle($handle)->setContents(['title' => $title]);

        $this->assertEquals($expectedTitle, $fieldset->title());
    }

    public function titleProvider()
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_saves_through_the_repository()
    {
        Event::fake();

        $fieldset = (new Fieldset)->setHandle('seo');

        FieldsetRepository::shouldReceive('find')->with($fieldset->handle());
        FieldsetRepository::shouldReceive('save')->with($fieldset)->once();

        $return = $fieldset->save();

        $this->assertEquals($fieldset, $return);

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

    /** @test */
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

    /** @test */
    public function it_saves_quietly()
    {
        Event::fake();

        $fieldset = (new Fieldset)->setHandle('seo');

        FieldsetRepository::shouldReceive('find')->with($fieldset->handle());
        FieldsetRepository::shouldReceive('save')->with($fieldset)->once();

        $return = $fieldset->saveQuietly();

        $this->assertEquals($fieldset, $return);

        Event::assertNotDispatched(FieldsetSaving::class);
        Event::assertNotDispatched(FieldsetSaved::class);
        Event::assertNotDispatched(FieldsetCreated::class);
    }

    /** @test */
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
}
