<?php

namespace Tests\Fieldtypes\Video;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Video\Providers;
use Tests\TestCase;

class ProvidersTest extends TestCase
{
    #[Test]
    public function it_gets_providers()
    {
        $providers = Providers::get();

        $this->assertSame('Cloudflare', $providers[0]['provider']);
    }
}
