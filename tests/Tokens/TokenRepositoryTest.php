<?php

namespace Tests\Tokens;

use Facades\Statamic\Tokens\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\File;
use Statamic\Tokens\FileTokenRepository;
use Tests\TestCase;

class TokenRepositoryTest extends TestCase
{
    private $tokens;

    public function setUp(): void
    {
        parent::setUp();

        $this->tokens = new FileTokenRepository;
    }

    #[Test]
    public function it_makes_a_token()
    {
        Generator::shouldReceive('generate')->never();

        $token = $this->tokens->make('test-token', 'HandlerClassName', ['foo' => 'bar', 'baz' => 'qux']);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals('HandlerClassName', $token->handler());
        $this->assertInstanceOf(Collection::class, $token->data());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $token->data()->all());
        $this->assertEquals('bar', $token->get('foo'));
        $this->assertEquals('qux', $token->get('baz'));
        $this->assertEquals('test-token', $token->token());
    }

    #[Test]
    public function it_generates_a_token_string_if_passing_in_null_when_making_a_token()
    {
        Generator::shouldReceive('generate')->once()->andReturn('test-token');

        $token = $this->tokens->make(null, 'HandlerClassName', ['foo' => 'bar', 'baz' => 'qux']);

        $this->assertEquals('test-token', $token->token());
    }

    #[Test]
    public function it_saves_a_token()
    {
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 0, 0, 0));

        $token = $this->tokens->make('test-token', 'The\\Test\\Class', ['foo' => 'bar', 'baz' => 'qux']);

        $return = $this->tokens->save($token);

        $expected = <<<YAML
handler: The\Test\Class
expires_at: 1577840400
data:
  foo: bar
  baz: qux

YAML;

        $this->assertStringEqualsFile(storage_path('statamic/tokens/test-token.yaml'), $expected);
        $this->assertTrue($return);
    }

    #[Test]
    public function it_deletes_a_token()
    {
        $token = tap($this->tokens->make('test-token', 'The\\Test\\Class', ['foo' => 'bar', 'baz' => 'qux']))->save();

        $this->assertNotNull($this->tokens->find('test-token'));

        $return = $this->tokens->delete($token);

        $this->assertNull($this->tokens->find('test-token'));
        $this->assertFalse(file_exists(storage_path('statamic/tokens/test-token.yaml')));
        $this->assertTrue($return);
    }

    #[Test]
    public function it_finds_a_token()
    {
        $contents = <<<YAML
handler: 'The\Test\Class'
expires_at: 1577849700
data:
  foo: bar
  baz: qux
YAML;

        File::put(storage_path('statamic/tokens/test-token.yaml'), $contents);

        $token = $this->tokens->find('test-token');

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals('The\Test\Class', $token->handler());
        $this->assertInstanceOf(Collection::class, $token->data());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $token->data()->all());
        $this->assertEquals('bar', $token->get('foo'));
        $this->assertEquals('qux', $token->get('baz'));
        $this->assertEquals('test-token', $token->token());
        $this->assertTrue($token->expiry()->eq(Carbon::create(2020, 1, 1, 3, 35)));
    }

    #[Test]
    public function attempting_to_find_a_non_existent_token_returns_null()
    {
        $this->assertNull($this->tokens->find('missing-token'));
    }

    #[Test]
    public function it_deletes_expired_tokens()
    {
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 3, 0, 0));

        $this->tokens->make('a', 'test')->save();
        $this->tokens->make('b', 'test')->expireAt(Carbon::now()->subMinute())->save();
        $this->tokens->make('c', 'test')->expireAt(Carbon::now()->subHour())->save();
        $this->tokens->make('d', 'test')->expireAt(Carbon::now()->addMinute())->save();

        $this->assertNotNull($this->tokens->find('a'));
        $this->assertNotNull($this->tokens->find('b'));
        $this->assertNotNull($this->tokens->find('c'));
        $this->assertNotNull($this->tokens->find('d'));

        $this->tokens->collectGarbage();

        $this->assertNotNull($this->tokens->find('a'));
        $this->assertNull($this->tokens->find('b'));
        $this->assertNull($this->tokens->find('c'));
        $this->assertNotNull($this->tokens->find('d'));
    }
}
