<?php

namespace Tests\Forms;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Event;
use Statamic\Events\FormCreated;
use Statamic\Events\FormCreating;
use Statamic\Events\FormSaved;
use Statamic\Events\FormSaving;
use Statamic\Facades\Form;
use Statamic\Fields\Blueprint;
use Tests\TestCase;

class FormTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Form::all()->each->delete();
    }

    /** @test */
    public function it_saves_a_form()
    {
        Event::fake();

        $blueprint = (new Blueprint)->setHandle('post')->save();

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie');

        $form->save();

        $this->assertEquals('contact_us', $form->handle());
        $this->assertEquals('Contact Us', $form->title());
        $this->assertEquals('winnie', $form->honeypot());

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

    /** @test */
    public function it_dispatches_form_created_only_once()
    {
        Event::fake();

        $blueprint = (new Blueprint)->setHandle('post')->save();

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

    /** @test */
    public function it_saves_quietly()
    {
        Event::fake();

        $blueprint = (new Blueprint)->setHandle('post')->save();

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->saveQuietly();

        Event::assertNotDispatched(FormCreating::class);
        Event::assertNotDispatched(FormSaving::class);
        Event::assertNotDispatched(FormSaved::class);
        Event::assertNotDispatched(FormCreated::class);
    }

    /** @test */
    public function if_creating_event_returns_false_the_form_doesnt_save()
    {
        Event::fake([FormCreated::class]);

        Event::listen(FormCreating::class, function () {
            return false;
        });

        $blueprint = (new Blueprint)->setHandle('post')->save();

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->save();

        Event::assertNotDispatched(FormCreated::class);
    }

    /** @test */
    public function if_saving_event_returns_false_the_form_doesnt_save()
    {
        Event::fake([FormSaved::class]);

        Event::listen(FormSaving::class, function () {
            return false;
        });

        $blueprint = (new Blueprint)->setHandle('post')->save();

        $form = Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->save();

        Event::assertNotDispatched(FormSaved::class);
    }

    /** @test */
    public function it_gets_all_forms()
    {
        $this->assertEmpty(Form::all());

        Form::make('contact_us')->save();
        Form::make('vote_for_canada')->save();

        $this->assertEquals(['contact_us', 'vote_for_canada'], Form::all()->map->handle()->all());
    }

    /** @test */
    public function it_has_default_honeypot()
    {
        $form = Form::make('contact_us');

        $this->assertEquals('honeypot', $form->honeypot());
    }

    /** @test */
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $form = Form::make('contact_us');

        $form
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $form->{$key}));
    }

    /** @test */
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

    /** @test */
    public function it_can_get_action_url()
    {
        $form = Form::make('contact_us');
        $route = route('statamic.forms.submit', $form->handle());

        $this->assertEquals($route, $form->actionUrl());
    }
}
