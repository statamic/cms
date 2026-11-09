<?php

namespace Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Rules\EmailWithoutPathCharacters;
use Tests\TestCase;

class EmailWithoutPathCharactersTest extends TestCase
{
    use ValidatesCustomRule;

    protected static $customRule = EmailWithoutPathCharacters::class;

    #[Test]
    public function it_passes_emails_without_path_characters()
    {
        $this->assertPasses('john@example.com');
        $this->assertPasses('john+tag@example.com');
        $this->assertPasses('"john doe"@example.com');
    }

    #[Test]
    public function it_fails_emails_with_path_characters()
    {
        $this->assertFails('a/b@example.com');
        $this->assertFails('/a@example.com');
        $this->assertFails('a@[x/y]');
        $this->assertFails('a\\b@example.com');
        $this->assertFails("a\0b@example.com");
    }

    #[Test]
    public function it_fails_with_the_email_validation_message()
    {
        $this->assertEquals(
            [trans('validation.email', ['attribute' => 'email'])],
            validator(['email' => 'a/b@example.com'], ['email' => new EmailWithoutPathCharacters])->errors()->get('email')
        );
    }
}
