<?php

namespace Tests\StaticCaching;

use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\DefaultUrlExcluder;
use Statamic\StaticCaching\UrlExcluder;
use Tests\TestCase;

class UrlExcluderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $mock = Mockery::mock(Cacher::class);
        $mock->shouldReceive('getBaseUrl')->andReturn('http://example.com');
        app()->instance(Cacher::class, $mock);
    }

    #[Test]
    public function it_defaults_to_the_default_excluder_when_only_urls_are_defined()
    {
        config(['statamic.static_caching.exclude' => [
            '/one',
            '/two',
        ]]);

        $excluder = app(UrlExcluder::class);

        $this->assertInstanceOf(DefaultUrlExcluder::class, $excluder);
        $this->assertEquals(['/one', '/two'], $excluder->getExclusions());
        $this->assertEquals('http://example.com', $excluder->getBaseUrl());
    }

    #[Test]
    public function it_defaults_to_the_default_excluder_when_no_class_is_provided()
    {
        config(['statamic.static_caching.exclude' => [
            'class' => null,
            'urls' => [
                '/one',
                '/two',
            ],
        ]]);

        $excluder = app(UrlExcluder::class);

        $this->assertInstanceOf(DefaultUrlExcluder::class, $excluder);
        $this->assertEquals(['/one', '/two'], $excluder->getExclusions());
    }

    #[Test]
    public function it_throws_error_when_referencing_class_that_doesnt_exist()
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Target class [NonExistentClass] does not exist.');

        config(['statamic.static_caching.exclude' => [
            'class' => 'NonExistentClass',
        ]]);

        app(UrlExcluder::class);
    }

    #[Test]
    public function it_gets_a_custom_class()
    {
        config(['statamic.static_caching.exclude' => [
            'class' => TestExcluder::class,
        ]]);

        $this->assertInstanceOf(TestExcluder::class, app(UrlExcluder::class));
    }
}

class TestExcluder implements UrlExcluder
{
    public function isExcluded(string $url): bool
    {
        return false;
    }
}
