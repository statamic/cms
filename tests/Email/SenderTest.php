<?php

namespace Tests\Email;

use Mockery;
use Tests\TestCase;
use Tests\FakesViews;
use Statamic\API\File;
use Statamic\API\Email;
use Illuminate\View\View;
use Statamic\Email\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\View\Engine;
use MailThief\Testing\InteractsWithMail;
use Statamic\Extensions\View\FileViewFinder;

class SenderTest extends TestCase
{
    use FakesViews;
    use InteractsWithMail;

    /** @test */
    function automagic_mail_gets_sent()
    {
        $this->markTestIncomplete();

        Email::create()
            ->to('john@recipient.com')
            ->from('jane@sender.com')
            ->with(['foo' => 'bar', 'baz' => 'qux'])
            ->automagic()
            ->send();

        $this->assertEquals(1, $this->getMessages()->count());
        $this->seeMessageFrom('jane@sender.com');
        $this->seeMessageFor('john@recipient.com');
        $this->assertMessageHtml("<strong>foo</strong>: bar<br><br>\n<strong>baz</strong>: qux<br><br>\n\n");
        $this->assertMessageText("foo: bar\nbaz: qux\n");
    }

    /** @test */
    function mail_gets_sent_using_text_html_hybrid_template()
    {
        $this->markTestIncomplete();

        File::shouldReceive('exists')
            ->with($templatePath = resource_path('views/email/my-email.html'))
            ->andReturnTrue();

        File::shouldReceive('get')
            ->with($templatePath)
            ->andReturn("text version foo: {{ foo }}\n---\nhtml version <b>foo</b>: {{ foo }}");

        Email::create()
            ->to('john@recipient.com')
            ->from('jane@sender.com')
            ->with(['foo' => 'bar', 'baz' => 'qux'])
            ->template('my-email')
            ->send();

        $this->assertEquals(1, $this->getMessages()->count());
        $this->seeMessageFrom('jane@sender.com');
        $this->seeMessageFor('john@recipient.com');
        $this->assertMessageHtml("html version <b>foo</b>: bar\n");
        $this->assertMessageText("text version foo: bar");
    }

    /** @test */
    function mail_gets_sent_with_fallback_view_if_specified_one_doesnt_exist()
    {
        $this->markTestIncomplete();

        $this->withFakeViews();
        tap($this->mockViewEngine, function ($m) {
            $m->shouldReceive('get')->with('email.built-in-view-html', Mockery::any())->andReturn('html email content');
            $m->shouldReceive('get')->with('email.built-in-view-text', Mockery::any())->andReturn('text email content');
        });
        File::shouldReceive('exists')->with(resource_path('views/email/built-in-view.html'))->andReturnFalse();

        Email::create()
            ->to('john@recipient.com')
            ->from('jane@sender.com')
            ->template('built-in-view')
            ->send();

        $this->assertEquals(1, $this->getMessages()->count());
        $this->seeMessageFrom('jane@sender.com');
        $this->seeMessageFor('john@recipient.com');
        $this->assertMessageHtml("html email content\n");
        $this->assertMessageText('text email content');
    }

    protected function assertMessageHtml($html)
    {
        $this->assertEquals($html, $this->lastMessage()->getBody());
    }

    protected function assertMessageText($text)
    {
        $this->assertEquals($text, $this->lastMessage()->getBody('text'));
    }
}