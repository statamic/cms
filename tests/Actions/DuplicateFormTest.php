<?php

namespace Tests\Actions;

use Statamic\Actions\DuplicateForm;
use Statamic\Facades\Form;
use Tests\TestCase;

class DuplicateFormTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Form::all()->each->delete();
    }

    public function tearDown(): void
    {
        Form::all()->each->delete();

        parent::tearDown();
    }

    /** @test */
    public function it_duplicates_a_form()
    {
        Form::make('a')->title('Original A')->honeypot('a')->save();
        Form::make('b')->title('Original B')->honeypot('b')->save();
        Form::make('c')->title('Original C')->honeypot('c')->save();

        $this->assertEquals([
            'a' => ['title' => 'Original A', 'honeypot' => 'a'],
            'b' => ['title' => 'Original B', 'honeypot' => 'b'],
            'c' => ['title' => 'Original C', 'honeypot' => 'c'],
        ], $this->formData());

        (new DuplicateForm)->run(
            collect([Form::find('b')]),
            ['title' => 'Duplicate of B', 'handle' => 'd']
        );

        $this->assertEquals([
            'a' => ['title' => 'Original A', 'honeypot' => 'a'],
            'b' => ['title' => 'Original B', 'honeypot' => 'b'],
            'c' => ['title' => 'Original C', 'honeypot' => 'c'],
            'd' => ['title' => 'Duplicate of B', 'honeypot' => 'b'],
        ], $this->formData());
    }

    private function formData()
    {
        return Form::all()->mapWithKeys(fn ($form) => [$form->handle() => [
            'title' => $form->title(),
            'honeypot' => $form->honeypot(),
        ]])->all();
    }
}
