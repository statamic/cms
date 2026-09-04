<?php

namespace Tests\Forms;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\FormCreated;
use Statamic\Events\FormCreating;
use Statamic\Events\FormDeleted;
use Statamic\Events\FormDeleting;
use Statamic\Events\FormSaved;
use Statamic\Events\FormSaving;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\Form;
use Statamic\Facades\YAML;
use Statamic\Forms\Fields\FormFields;
use Tests\TestCase;

class FormTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Form::all()->each->delete();
    }

    #[Test]
    public function it_falls_back_to_the_handle_for_the_title()
    {
        $form = Form::make('contact_us');

        $this->assertEquals('Contact_us', $form->title());

        $form->title('Contact Us');

        $this->assertEquals('Contact Us', $form->title());
    }

    #[Test]
    public function it_doesnt_save_the_fallback_title()
    {
        $form = Form::make('contact_us');

        $form->save();

        $this->assertStringNotContainsString('title', File::get($form->path()));
    }

    #[Test]
    public function it_saves_a_form()
    {
        Event::fake();

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->data([
                'foo' => 'bar',
                'roo' => 'rar',
            ]);

        $form->save();

        $this->assertEquals('contact_us', $form->handle());
        $this->assertEquals('Contact Us', $form->title());
        $this->assertEquals('winnie', $form->honeypot());
        $this->assertEquals([
            'foo' => 'bar',
            'roo' => 'rar',
        ], $form->data()->all());

        Event::assertDispatched(FormCreating::class, function ($event) use ($form) {
            return $event->form === $form;
        });

        Event::assertDispatched(FormSaving::class, function ($event) use ($form) {
            return $event->form === $form;
        });

        Event::assertDispatched(FormCreated::class, function ($event) use ($form) {
            return $event->form === $form;
        });

        Event::assertDispatched(FormSaved::class, function ($event) use ($form) {
            return $event->form === $form;
        });
    }

    #[Test]
    public function it_dispatches_form_created_only_once()
    {
        Event::fake();

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie');

        Form::shouldReceive('save')->with($form);
        Form::shouldReceive('find')->with($form->handle())->times(3)->andReturn(null, $form, $form);

        $form->save();
        $form->save();
        $form->save();

        Event::assertDispatched(FormSaved::class, 3);
        Event::assertDispatched(FormCreated::class, 1);
    }

    #[Test]
    public function it_deletes_blueprint_after_saving()
    {
        Blueprint::make()->setHandle('contact_us')->setNamespace('forms')->save();

        $this->assertNotNull(Blueprint::find('forms.contact_us'));

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->data([
                'foo' => 'bar',
                'roo' => 'rar',
            ]);

        $form->save();

        $this->assertNull(Blueprint::find('forms.contact_us'));
    }

    #[Test]
    public function it_saves_quietly()
    {
        Event::fake();

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->saveQuietly();

        Event::assertNotDispatched(FormCreating::class);
        Event::assertNotDispatched(FormSaving::class);
        Event::assertNotDispatched(FormSaved::class);
        Event::assertNotDispatched(FormCreated::class);
    }

    #[Test]
    public function if_creating_event_returns_false_the_form_doesnt_save()
    {
        Event::fake([FormCreated::class]);

        Event::listen(FormCreating::class, function () {
            return false;
        });

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->save();

        Event::assertNotDispatched(FormCreated::class);
    }

    #[Test]
    public function if_saving_event_returns_false_the_form_doesnt_save()
    {
        Event::fake([FormSaved::class]);

        Event::listen(FormSaving::class, function () {
            return false;
        });

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->save();

        Event::assertNotDispatched(FormSaved::class);
    }

    #[Test]
    public function it_gets_all_forms()
    {
        $this->assertEmpty(Form::all());

        Form::make('contact_us')->save();
        Form::make('vote_for_canada')->save();

        $this->assertEquals(['contact_us', 'vote_for_canada'], Form::all()->map->handle()->all());
    }

    #[Test]
    public function it_has_default_honeypot()
    {
        $form = Form::make('contact_us');

        $this->assertEquals('honeypot', $form->honeypot());
    }

    #[Test]
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $form = Form::make('contact_us');

        $form
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $form->{$key}));
    }

    #[Test]
    public function it_is_arrayable()
    {
        $form = Form::make('contact_us');

        $this->assertInstanceOf(Arrayable::class, $form);

        $expectedAugmented = $form->toAugmentedCollection();

        $array = $form->toArray();

        $this->assertCount($expectedAugmented->count(), $array);

        collect($array)
            ->each(function ($value, $key) use ($form) {
                $expected = $form->{$key};
                $expected = $expected instanceof Arrayable ? $expected->toArray() : $expected;
                $this->assertEquals($expected, $value);
            });
    }

    #[Test]
    public function it_can_get_action_url()
    {
        $form = Form::make('contact_us');
        $route = route('statamic.forms.submit', $form->handle());

        $this->assertEquals($route, $form->actionUrl());
    }

    #[Test]
    public function it_fires_a_deleting_event()
    {
        Event::fake();

        $form = Form::make('contact_us');

        $form->delete();

        Event::assertDispatched(FormDeleting::class, function ($event) use ($form) {
            return $event->form === $form;
        });
    }

    #[Test]
    public function it_does_not_delete_when_a_deleting_event_returns_false()
    {
        Form::spy();
        Event::fake([FormDeleted::class]);

        Event::listen(FormDeleting::class, function () {
            return false;
        });

        $form = new \Statamic\Forms\Form('test');

        $return = $form->delete();

        $this->assertFalse($return);
        Form::shouldNotHaveReceived('delete');
        Event::assertNotDispatched(FormDeleted::class);
    }

    #[Test]
    public function it_deletes_quietly()
    {
        Event::fake();

        $form = Form::make('contact_us');

        $return = $form->deleteQuietly();

        Event::assertNotDispatched(FormDeleting::class);
        Event::assertNotDispatched(FormDeleted::class);

        $this->assertTrue($return);
    }

    #[Test]
    public function it_clones_internal_collections()
    {
        $form = Form::make('contact_us');
        $form->set('foo', 'A');
        $form->setSupplement('bar', 'A');

        $clone = clone $form;
        $clone->set('foo', 'B');
        $clone->setSupplement('bar', 'B');

        $this->assertEquals('A', $form->get('foo'));
        $this->assertEquals('B', $clone->get('foo'));

        $this->assertEquals('A', $form->getSupplement('bar'));
        $this->assertEquals('B', $clone->getSupplement('bar'));
    }

    #[Test]
    public function it_gets_and_sets_form_fields()
    {
        $fields = [
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                    ],
                ],
            ],
        ];

        $form = Form::make('contact_us')->formFields($fields);

        $formFields = $form->formFields();

        $this->assertInstanceOf(FormFields::class, $formFields);
        $this->assertEquals($fields, $formFields->contents());
    }

    #[Test]
    public function it_saves_form_fields_to_yaml()
    {
        $fields = [
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                    ],
                ],
            ],
        ];

        $form = tap(Form::make('contact_us')
            ->title('Contact Us')
            ->formFields($fields))
            ->save();

        $saved = YAML::parse(File::get($form->path()));

        $this->assertEquals($fields, $saved['fields']);
    }

    #[Test]
    public function it_hydrates_form_fields_from_yaml()
    {
        $fields = [
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                    ],
                ],
            ],
        ];

        Form::make('contact_us')
            ->title('Contact Us')
            ->formFields($fields)
            ->save();

        $form = Form::find('contact_us');

        $formFields = $form->formFields();

        $this->assertInstanceOf(FormFields::class, $formFields);
        $this->assertCount(2, $formFields->items());
        $this->assertEquals('email', $formFields->field('email')->handle());
        $this->assertEquals('name', $formFields->field('name')->handle());
    }

    #[Test]
    public function it_saves_charts_to_yaml_and_hydrates_them_back()
    {
        $charts = [
            ['field' => 'color', 'chart' => 'pie'],
            ['field' => 'rating', 'chart' => 'horizontal_bar'],
        ];

        $form = tap(Form::make('contact_us')->charts($charts))->save();

        $this->assertEquals($charts, YAML::parse(File::get($form->path()))['charts']);
        $this->assertEquals($charts, Form::find('contact_us')->charts());
    }

    #[Test]
    public function it_saves_an_explicitly_emptied_charts_list()
    {
        $form = tap(Form::make('contact_us')->charts([]))->save();

        $this->assertEquals([], YAML::parse(File::get($form->path()))['charts']);
        $this->assertEquals([], Form::find('contact_us')->charts());
    }

    #[Test]
    public function it_doesnt_save_charts_when_never_configured()
    {
        $form = tap(Form::make('contact_us'))->save();

        $this->assertArrayNotHasKey('charts', YAML::parse(File::get($form->path())));
        $this->assertNull(Form::find('contact_us')->charts());
    }
}
