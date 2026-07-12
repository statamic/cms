<?php

namespace Tests\Fieldtypes\Concerns;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Data as DataFacade;
use Statamic\Fieldtypes\Concerns\ResolvesStatamicUrls;
use Tests\TestCase;

class ResolvesStatamicUrlsTest extends TestCase
{
    private $testClass;

    public function setUp(): void
    {
        parent::setUp();

        $this->testClass = new class
        {
            use ResolvesStatamicUrls;

            public function resolve(string $content)
            {
                return $this->resolveStatamicUrls($content);
            }
        };
    }

    #[Test]
    public function it_calls_data_find_with_correct_id()
    {
        $data = Mockery::mock();
        $data->shouldReceive('url')->andReturn('/some/url');

        DataFacade::shouldReceive('find')
            ->once()
            ->with('foo::bar/baz.ext')
            ->andReturn($data);

        $content = '[link](statamic://foo::bar/baz.ext)';
        $result = $this->testClass->resolve($content);

        $this->assertEquals('[link](/some/url)', $result);
    }

    #[Test]
    public function it_handles_non_existent_data()
    {
        DataFacade::shouldReceive('find')
            ->once()
            ->with('non-existent')
            ->andReturn(null);

        $content = '[link](statamic://non-existent)';
        $result = $this->testClass->resolve($content);

        $this->assertEquals('[link]()', $result);
    }

    #[Test]
    public function it_handles_multiple_urls()
    {
        $data1 = Mockery::mock();
        $data1->shouldReceive('url')->andReturn('/url-1');

        $data2 = Mockery::mock();
        $data2->shouldReceive('url')->andReturn('/url-2');

        DataFacade::shouldReceive('find')
            ->once()
            ->with('id-1')
            ->andReturn($data1);

        DataFacade::shouldReceive('find')
            ->once()
            ->with('id-2')
            ->andReturn($data2);

        $content = '[link1](statamic://id-1) and <img src="statamic://id-2" />';
        $result = $this->testClass->resolve($content);

        $this->assertEquals('[link1](/url-1) and <img src="/url-2" />', $result);
    }

    #[Test]
    public function it_maintains_hash_fragments()
    {
        $data = Mockery::mock();
        $data->shouldReceive('url')->andReturn('/some/page');

        DataFacade::shouldReceive('find')
            ->once()
            ->with('entry::123')
            ->andReturn($data);

        $content = '[link](statamic://entry::123#section)';
        $result = $this->testClass->resolve($content);

        $this->assertEquals('[link](/some/page#section)', $result);
    }

    #[Test]
    public function it_maintains_query_strings()
    {
        $data = Mockery::mock();
        $data->shouldReceive('url')->andReturn('/some/page');

        DataFacade::shouldReceive('find')
            ->once()
            ->with('entry::123')
            ->andReturn($data);

        $content = '[link](statamic://entry::123?foo=bar)';
        $result = $this->testClass->resolve($content);

        $this->assertEquals('[link](/some/page?foo=bar)', $result);
    }

    #[Test]
    public function it_maintains_query_strings_and_hash_fragments()
    {
        $data = Mockery::mock();
        $data->shouldReceive('url')->andReturn('/some/page');

        DataFacade::shouldReceive('find')
            ->once()
            ->with('entry::123')
            ->andReturn($data);

        $content = '[link](statamic://entry::123?foo=bar#section)';
        $result = $this->testClass->resolve($content);

        $this->assertEquals('[link](/some/page?foo=bar#section)', $result);
    }
}
