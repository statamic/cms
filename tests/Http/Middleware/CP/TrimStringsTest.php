<?php

namespace Tests\Http\Middleware\CP;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Http\Middleware\CP\TrimStrings;
use Tests\TestCase;

class TrimStringsTest extends TestCase
{
    #[Test]
    public function it_trims_strings()
    {
        $request = $this->handle(new TrimStrings, [
            'title' => '  Hello  ',
            'nested' => ['value' => '  World  '],
        ]);

        $this->assertSame('Hello', $request->input('title'));
        $this->assertSame('World', $request->input('nested.value'));
    }

    #[Test]
    public function it_does_not_trim_passwords()
    {
        $request = $this->handle(new TrimStrings, [
            'password' => '  secret  ',
            'password_confirmation' => '  secret  ',
            'current_password' => '  old  ',
        ]);

        $this->assertSame('  secret  ', $request->input('password'));
        $this->assertSame('  secret  ', $request->input('password_confirmation'));
        $this->assertSame('  old  ', $request->input('current_password'));
    }

    #[Test]
    public function it_does_not_trim_bard_text_nodes()
    {
        $request = $this->handle(new TrimStrings, [
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello '],
                        ['type' => 'text', 'text' => ' world', 'marks' => [['type' => 'bold', 'attrs' => ['class' => '  foo  ']]]],
                    ],
                ],
            ],
            'not_a_text_node' => ['type' => 'paragraph', 'text' => '  trimmed  '],
        ]);

        $this->assertSame('Hello ', $request->input('content.0.content.0.text'));
        $this->assertSame(' world', $request->input('content.0.content.1.text'));
        $this->assertSame('foo', $request->input('content.0.content.1.marks.0.attrs.class'));
        $this->assertSame('trimmed', $request->input('not_a_text_node.text'));
    }

    #[Test]
    public function it_does_not_trim_a_text_node_at_the_root_of_the_request()
    {
        $request = $this->handle(new TrimStrings, ['type' => 'text', 'text' => '  Hello  ']);

        $this->assertSame('  Hello  ', $request->input('text'));
    }

    #[Test]
    public function text_nodes_do_not_affect_subsequent_requests()
    {
        $middleware = new TrimStrings;

        $this->handle($middleware, ['foo' => ['type' => 'text', 'text' => '  untrimmed  ']]);
        $request = $this->handle($middleware, ['foo' => ['text' => '  trimmed  ']]);

        $this->assertSame('trimmed', $request->input('foo.text'));
    }

    private function handle(TrimStrings $middleware, array $data): Request
    {
        $request = Request::create('/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $middleware->handle($request, fn () => null);

        return $request;
    }
}
