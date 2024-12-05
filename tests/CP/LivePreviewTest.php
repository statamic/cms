<?php

namespace Tests\CP;

use Facades\Statamic\CP\LivePreview;
use Facades\Statamic\Tokens\Generator;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Token;
use Statamic\Tokens\Handlers\LivePreview as LivePreviewHandler;
use Tests\TestCase;

class LivePreviewTest extends TestCase
{
    #[Test]
    public function it_tokenizes_an_entry()
    {
        optional(Token::find('test-token'))->delete();

        LivePreview::tokenize('test-token', 'item');

        $this->assertNotNull($token = Token::find('test-token'));
        $this->assertEquals(LivePreviewHandler::class, $token->handler());

        $this->assertSame('item', Cache::get('statamic.live-preview.test-token'));
    }

    #[Test]
    public function it_tokenizes_an_entry_without_an_existing_token()
    {
        Generator::shouldReceive('generate')->andReturn('test-token');

        optional(Token::find('test-token'))->delete();

        LivePreview::tokenize(null, 'item');

        $this->assertNotNull($token = Token::find('test-token'));
        $this->assertEquals(LivePreviewHandler::class, $token->handler());

        $this->assertSame('item', Cache::get('statamic.live-preview.test-token'));
        $this->assertSame('item', LivePreview::item($token));
    }
}
