<?php

use Illuminate\Support\Facades\Validator;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UniqueUserValueTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_fails_when_theres_a_duplicate_user_value()
    {
        User::make()->email('foo@bar.com')->save();

        $this->assertTrue(Validator::make(
            ['email' => 'foo@bar.com'],
            ['email' => 'unique_user_value']
        )->fails());

        $this->assertTrue(Validator::make(
            ['slug' => 'bar@bar.com'],
            ['slug' => 'unique_user_value']
        )->passes());
    }

    /** @test */
    public function it_passes_when_updating()
    {
        User::make()->email('foo@bar.com')->id('123')->save();

        $this->assertTrue(Validator::make(
            ['email' => 'foo@bar.com'],
            ['email' => 'unique_user_value:123']
        )->passes());
    }

    /** @test */
    public function it_supports_overwriting_the_column()
    {
        User::make()->email('foo@bar.com')->save();

        $this->assertTrue(Validator::make(
            ['baz' => 'foo@bar.com'],
            ['baz' => 'unique_user_value:null,email']
        )->fails());
    }
}
