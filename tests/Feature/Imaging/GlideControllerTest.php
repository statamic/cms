<?php

namespace Tests\Feature\Imaging;

use League\Glide\Signatures\SignatureFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Config;
use Statamic\Imaging\ImageGenerator;
use Tests\TestCase;

class GlideControllerTest extends TestCase
{
    private $generatedParams;

    public function setUp(): void
    {
        parent::setUp();

        config(['statamic.assets.image_manipulation.secure' => true]);

        $this->generatedParams = null;

        $this->mock(ImageGenerator::class, function ($mock) {
            $mock->shouldReceive('generateByPath')->andReturnUsing(function ($path, $params) {
                $this->generatedParams = $params;

                return 'foo.jpg';
            });
        });
    }

    private function signedParams(array $params)
    {
        return SignatureFactory::create(Config::getAppKey())->addSignature('/img/foo.jpg', $params);
    }

    #[Test]
    public function it_uses_the_signed_query_params()
    {
        $params = $this->signedParams(['w' => '100']);

        $this->get('/img/foo.jpg?'.http_build_query($params));

        $this->assertEquals($params, $this->generatedParams);
    }

    #[Test]
    public function it_rejects_an_invalid_signature()
    {
        $this->get('/img/foo.jpg?w=100&s=invalid')->assertStatus(400);

        $this->assertNull($this->generatedParams);
    }

    #[Test]
    public function json_body_cannot_override_signed_params()
    {
        $params = $this->signedParams(['w' => '100']);

        $this->call('GET', '/img/foo.jpg', $params, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['w' => '9999', 'mark' => 'asset::foo']));

        $this->assertEquals($params, $this->generatedParams);
    }

    #[Test]
    public function method_spoofed_form_body_cannot_override_signed_params()
    {
        $params = $this->signedParams(['w' => '100']);

        $this->call('POST', '/img/foo.jpg?'.http_build_query($params), [
            '_method' => 'GET',
            'w' => '9999',
            'mark' => 'asset::foo',
        ]);

        $this->assertEquals($params, $this->generatedParams);
    }
}
