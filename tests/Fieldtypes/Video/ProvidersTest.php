<?php

namespace Tests\Fieldtypes\Video;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Video\Providers;
use Tests\TestCase;

class ProvidersTest extends TestCase
{
    #[Test]
    public function it_gets_options_with_a_stable_value_and_a_translated_label()
    {
        $options = Providers::options();

        $this->assertContains(['value' => 'Youtube', 'label' => 'Youtube'], $options);
        $this->assertContains(['value' => 'cloudflare', 'label' => 'Cloudflare Stream'], $options);
        $this->assertContains(['value' => 'file', 'label' => 'Video File'], $options);
    }

    #[Test]
    public function it_does_not_offer_unsupported_as_an_option()
    {
        $this->assertNotContains(Providers::UNSUPPORTED, collect(Providers::options())->pluck('value')->all());
    }

    #[Test]
    public function it_only_registers_video_providers()
    {
        $providers = collect(Providers::options())->pluck('value');

        $this->assertContains('Vimeo', $providers);
        $this->assertNotContains('Figma', $providers);
        $this->assertNotContains('Scribd', $providers);
    }
}
