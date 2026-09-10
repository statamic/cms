<?php

namespace Tests\Validation;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Rules\UniqueUserValue;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UniqueUserValueTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_fails_when_theres_a_duplicate_user_value()
    {
        User::make()->email('foo@bar.com')->save();

        $this->assertTrue(Validator::make(
            ['email' => 'foo@bar.com'],
            ['email' => new UniqueUserValue]
        )->fails());

        $this->assertTrue(Validator::make(
            ['slug' => 'bar@bar.com'],
            ['slug' => new UniqueUserValue]
        )->passes());
    }

    #[Test]
    public function it_passes_when_updating()
    {
        User::make()->email('foo@bar.com')->id('123')->save();

        $this->assertTrue(Validator::make(
            ['email' => 'foo@bar.com'],
            ['email' => new UniqueUserValue(except: 123)]
        )->passes());
    }

    #[Test]
    public function it_supports_overwriting_the_column()
    {
        User::make()->email('foo@bar.com')->save();

        $this->assertTrue(Validator::make(
            ['baz' => 'foo@bar.com'],
            ['baz' => new UniqueUserValue(column: 'email')]
        )->fails());
    }

    #[Test]
    public function it_uses_the_app_translation_when_one_exists()
    {
        User::make()->email('foo@bar.com')->save();

        $validator = Validator::make(
            ['email' => 'foo@bar.com'],
            ['email' => new UniqueUserValue]
        );

        $this->assertEquals('This value has already been taken.', $validator->errors()->first('email'));

        Lang::addLines(['validation.unique_user_value' => 'This email has already been taken.'], 'en');

        $validator = Validator::make(
            ['email' => 'foo@bar.com'],
            ['email' => new UniqueUserValue]
        );

        $this->assertEquals('This email has already been taken.', $validator->errors()->first('email'));
    }
}
