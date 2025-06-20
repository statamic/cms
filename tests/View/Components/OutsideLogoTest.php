<?php

namespace Tests\View\Components;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('view-components')]
class OutsideLogoTest extends TestCase
{
    #[Test]
    public function it_renders_default_statamic_logo_when_no_custom_logo_is_set()
    {
        $this->assertStringContainsString(
            'statamic-wordmark',
            Blade::render('<x-outside-logo />')
        );
    }

    #[Test]
    public function it_renders_custom_logo_when_configured()
    {
        config(['statamic.cp.custom_logo_url' => 'https://example.com/logo.png']);

        $rendered = Blade::render('<x-outside-logo />');

        $this->assertStringContainsString('https://example.com/logo.png', $rendered);
        $this->assertStringContainsString('white-label-logo', $rendered);
    }

    #[Test]
    public function it_renders_custom_dark_logo_when_configured()
    {
        config([
            'statamic.cp.custom_logo_url' => 'https://example.com/logo.png',
            'statamic.cp.custom_dark_logo_url' => 'https://example.com/logo-dark.png',
        ]);

        $rendered = Blade::render('<x-outside-logo />');

        $this->assertStringContainsString('https://example.com/logo.png', $rendered);
        $this->assertStringContainsString('https://example.com/logo-dark.png', $rendered);
        $this->assertStringContainsString('dark:hidden', $rendered);
        $this->assertStringContainsString('hidden dark:block', $rendered);
    }

    #[Test]
    public function it_renders_custom_logo_text_when_configured()
    {
        config(['statamic.cp.custom_logo_text' => 'My Custom CMS']);

        $rendered = Blade::render('<x-outside-logo />');

        $this->assertStringContainsString('My Custom CMS', $rendered);
        $this->assertStringContainsString('text-lg font-medium opacity-50', $rendered);
    }
}
