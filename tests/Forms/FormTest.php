<?php

namespace Tests\Forms;

use Illuminate\Support\Facades\Event;
use Statamic\Events\FormCreated;
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

        Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->save();

        $form = Form::find('contact_us');

        $this->assertEquals('contact_us', $form->handle());
        $this->assertEquals('Contact Us', $form->title());
        $this->assertEquals('winnie', $form->honeypot());

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

        Event::assertNotDispatched(FormSaving::class);
        Event::assertNotDispatched(FormSaved::class);
        Event::assertNotDispatched(FormCreated::class);
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
}
