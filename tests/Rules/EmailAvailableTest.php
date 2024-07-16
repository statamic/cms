<?php

namespace Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Rules\EmailAvailable;
use Tests\TestCase;

class EmailAvailableTest extends TestCase
{
    use ValidatesCustomRule;

    protected static $customRule = EmailAvailable::class;

    public function setUp(): void
    {
        parent::setUp();

        User::make()->email('frodo@lotr.com')->save();
    }

    public function tearDown(): void
    {
        User::all()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_validates_handles()
    {
        $this->assertPasses('gandalf@lotr.com');
        $this->assertPasses('aragorn@lotr.com');
        $this->assertPasses('samwise@lotr.com');

        $this->assertFails('frodo@lotr.com');
    }

    #[Test]
    public function it_outputs_helpful_validation_error()
    {
        $this->assertValidationErrorOutput(trans('statamic::validation.email_available'), 'frodo@lotr.com');
    }
}
