<?php

namespace Tests\CP\Controllers\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class VideoFieldtypeControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('valuesProvider')]
    public function it_creates_a_video(array $queryParams, array $video)
    {
        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->get(cp_route('video.details', $queryParams))
            ->assertOK()
            ->assertJson($video, $strict = true);
    }

    public static function valuesProvider()
    {
        return [
            [[], ['embed' => null, 'id' => null, 'provider' => 'Not Supported']],
            [['url' => 'https://www.youtube.com/watch?v=FK3dav4bA4s'], ['id' => null, 'provider' => 'Youtube']],
            [['url' => 'cloudflare:1234'], [
                'embed' => '<iframe src="https://iframe.cloudflarestream.com/1234" frameborder="0" allow="fullscreen" style="height: 100%; width: 100%;"></iframe>',
                'id' => '1234',
                'provider' => 'Cloudflare',
            ]],
        ];
    }
}
