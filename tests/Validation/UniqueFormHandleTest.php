<?php

namespace Tests\Validation;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Rules\UniqueFormHandle;
use Tests\TestCase;

class UniqueFormHandleTest extends TestCase
{
    public function tearDown(): void
    {
        Form::all()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_fails_when_theres_a_duplicate_form_handle()
    {
        Form::make('contact')->save();

        $this->assertTrue(Validator::make(
            ['handle' => 'contact'],
            ['handle' => new UniqueFormHandle]
        )->fails());

        $this->assertTrue(Validator::make(
            ['handle' => 'newsletter'],
            ['handle' => new UniqueFormHandle]
        )->passes());
    }

    #[Test]
    public function it_uses_the_app_translation_when_one_exists()
    {
        Form::make('contact')->save();

        $validator = Validator::make(
            ['handle' => 'contact'],
            ['handle' => new UniqueFormHandle]
        );

        $this->assertEquals('This value has already been taken.', $validator->errors()->first('handle'));

        Lang::addLines(['validation.unique_form_handle' => 'This handle has already been taken.'], 'en');

        $validator = Validator::make(
            ['handle' => 'contact'],
            ['handle' => new UniqueFormHandle]
        );

        $this->assertEquals('This handle has already been taken.', $validator->errors()->first('handle'));
    }
}
