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
    public function it_gets_video_details(array $queryParams, array $video)
    {
        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->get(cp_route('video.details', $queryParams))
            ->assertOK()
            ->assertExactJson($video);
    }

    public static function valuesProvider()
    {
        return [
            'no value' => [[], ['embed_url' => null, 'id' => null, 'provider' => 'unsupported', 'url' => null]],
            'youtube' => [['value' => 'https://www.youtube.com/watch?v=FK3dav4bA4s'], [
                'embed_url' => 'https://www.youtube.com/embed/FK3dav4bA4s?feature=oembed',
                'id' => null,
                'provider' => 'Youtube',
                'url' => 'https://www.youtube.com/watch?v=FK3dav4bA4s',
            ]],
            'cloudflare' => [['value' => 'cloudflare:1234'], [
                'embed_url' => 'https://iframe.cloudflarestream.com/1234',
                'id' => '1234',
                'provider' => 'cloudflare',
                'url' => 'cloudflare:1234',
            ]],
        ];
    }

    #[Test]
    public function it_requires_authentication()
    {
        $this
            ->get(cp_route('video.details', ['value' => 'https://www.youtube.com/watch?v=FK3dav4bA4s']))
            ->assertRedirect(cp_route('login'));
    }
}
