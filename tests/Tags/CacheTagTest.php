<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Statamic\Facades\Config;
use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Tests\TestCase;

class CacheTagTest extends TestCase
{
    /** @test */
    public function it_caches_its_contents()
    {
        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('get')->once()->andReturn(null)
                ->shouldReceive('put')->once()->with(Mockery::any(), 'expensive', Mockery::any())
                ->getMock()
        );
        $this->assertEquals('expensive', $this->tag('{{ cache }}expensive{{ /cache }}'));
    }

    /** @test */
    public function it_skips_the_cache_if_cache_tags_are_not_enabled()
    {
        Config::set('statamic.system.cache_tags_enabled', false);

        Cache::shouldReceive('store')->never();

        $this->assertEquals('expensive', $this->tag('{{ cache }}expensive{{ /cache }}'));
    }

    // Site::setConfig(['sites' => [
    //     'en' => ['url' => '/'],
    //     'fr' => ['url' => '/fr'],
    // ]]);

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}
