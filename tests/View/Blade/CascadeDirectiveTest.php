<?php

namespace Tests\View\Blade;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\CascadeDataNotFoundException;
use Statamic\View\Blade\CascadeDirective;
use Tests\TestCase;

class CascadeDirectiveTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_gets_all_data()
    {
        $data = CascadeDirective::handle();

        $this->assertArrayHasKey('environment', $data);
        $this->assertArrayHasKey('config', $data);
        $this->assertArrayHasKey('site', $data);
    }

    #[Test]
    public function it_gets_specific_data()
    {
        $data = CascadeDirective::handle([
            'homepage',
            'is_homepage',
        ]);

        $this->assertCount(2, $data);
        $this->assertArrayHasKey('homepage', $data);
        $this->assertArrayHasKey('is_homepage', $data);
        $this->assertEquals($data['homepage'], 'http://localhost');
        $this->assertEquals($data['is_homepage'], true);
    }

    #[Test]
    public function it_throws_exception_for_missing_data()
    {
        $this->expectException(CascadeDataNotFoundException::class);

        CascadeDirective::handle([
            'live_preview',
        ]);
    }

    #[Test]
    public function it_uses_default_for_missing_data()
    {
        $data = CascadeDirective::handle([
            'live_preview' => false,
        ]);

        $this->assertCount(1, $data);
        $this->assertArrayHasKey('live_preview', $data);
        $this->assertEquals($data['live_preview'], false);
    }
}
