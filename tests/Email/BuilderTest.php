<?php

namespace Tests\Email;

use Illuminate\Contracts\Mail\Mailer;
use Statamic\Email\Builder;
use Statamic\Email\Message;
use Statamic\Email\Sender;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    /** @test */
    public function email_only()
    {
        $b = $this->builder()->to('john@example.com');

        $this->assertEquals([
            ['john@example.com', null]
        ], $b->message()->to());
    }

    /** @test */
    public function email_and_name()
    {
        $b = $this->builder()->to('john@example.com', 'John Doe');

        $this->assertEquals([
            ['john@example.com', 'John Doe']
        ], $b->message()->to());
    }

    /** @test */
    public function multiple_emails()
    {
        $b = $this->builder()
            ->to('john@example.com')
            ->to('jane@example.com');

        $this->assertEquals([['john@example.com', null], ['jane@example.com', null]], $b->message()->to());
    }

    /** @test */
    public function multiple_emails_with_names()
    {
        $b = $this->builder()
            ->to('john@example.com', 'John Doe')
            ->to('jane@example.com', 'Jane Doe');

        $this->assertEquals([
            ['john@example.com', 'John Doe'],
            ['jane@example.com', 'Jane Doe']
        ], $b->message()->to());
    }

    /** @test */
    public function multiple_emails_some_names()
    {
        $b = $this->builder()
            ->to('john@example.com', 'John Doe')
            ->to('jane@example.com');

        $this->assertEquals([
            ['john@example.com', 'John Doe'],
            ['jane@example.com', null]
        ], $b->message()->to());
    }

    /** @test */
    public function email_and_name_in_bracket_format()
    {
        $b = $this->builder()->to('John Doe <john@example.com>');

        $this->assertEquals([
            ['john@example.com', 'John Doe']
        ], $b->message()->to());
    }

    /** @test */
    public function multiple_comma_delimited_emails()
    {
        $b = $this->builder()->to('john@example.com, jane@example.com');

        $this->assertEquals([
            ['john@example.com', null],
            ['jane@example.com', null]
        ], $b->message()->to());
    }

    /** @test */
    public function multiple_comma_delimited_emails_with_names()
    {
        $b = $this->builder()->to('John Doe <john@example.com>, Jane Doe <jane@example.com>');

        $this->assertEquals([
            ['john@example.com', 'John Doe'],
            ['jane@example.com', 'Jane Doe']
        ], $b->message()->to());
    }

    /** @test */
    public function multiple_comma_delimited_emails_with_some_names()
    {
        $b = $this->builder()->to('John Doe <john@example.com>, jane@example.com');

        $this->assertEquals([
            ['john@example.com', 'John Doe'],
            ['jane@example.com', null]
        ], $b->message()->to());
    }

    /** @test */
    public function multiple_comma_delimited_emails_with_some_names_in_cc_field()
    {
        $b = $this->builder()->cc('John Doe <john@example.com>, jane@example.com');

        $this->assertEquals([
            ['john@example.com', 'John Doe'],
            ['jane@example.com', null]
        ], $b->message()->cc());
    }

    /** @test */
    public function multiple_comma_delimited_emails_with_some_names_in_bcc_field()
    {
        $b = $this->builder()->bcc('John Doe <john@example.com>, jane@example.com');

        $this->assertEquals([
            ['john@example.com', 'John Doe'],
            ['jane@example.com', null]
        ], $b->message()->bcc());
    }

    private function builder()
    {
        $sender = \Mockery::mock(Sender::class);
        return new Builder(new Message($sender));
    }
}
