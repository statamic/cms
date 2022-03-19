<?php

namespace Tests\Forms;

use Illuminate\Contracts\Support\Arrayable;
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
        $blueprint = (new Blueprint)->setHandle('post')->save();

        Form::make('contact_us')
            ->title('Contact Us')
            ->honeypot('winnie')
            ->save();

        $form = Form::find('contact_us');

        $this->assertEquals('contact_us', $form->handle());
        $this->assertEquals('Contact Us', $form->title());
        $this->assertEquals('winnie', $form->honeypot());
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
}
