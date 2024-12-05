<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class TrackableEmbedUrlTest extends TestCase
{
    #[Test]
    public function it_leaves_urls_from_unknown_providers_untouched()
    {
        $this->assertEquals('https://statamic.com/video/hello', $this->embed('https://statamic.com/video/hello'));
    }

    #[Test]
    public function it_transforms_vimeo_urls()
    {
        $embedUrl = 'https://player.vimeo.com/video/71360261';

        $this->assertEquals($embedUrl, $this->embed('https://vimeo.com/71360261'));

        $this->assertEquals(
            $embedUrl.'?foo=bar',
            $this->embed('https://vimeo.com/71360261?foo=bar'),
            'It appends the do not track query param if a query string already exists.'
        );
    }

    #[Test]
    public function it_transforms_youtube_urls()
    {
        $embedUrl = 'https://www.youtube.com/embed/s72r_wu_NVY';

        $this->assertEquals(
            $embedUrl,
            $this->embed('https://www.youtube.com/watch?v=s72r_wu_NVY')
        );
        $this->assertEquals(
            $embedUrl,
            $this->embed('https://youtu.be/s72r_wu_NVY'),
            'It transforms shortened youtube video sharing links'
        );

        $this->assertEquals(
            $embedUrl.'?start=559',
            $this->embed('https://youtu.be/s72r_wu_NVY?t=559'),
            'It transforms the start time parameter of shortened sharing links'
        );
    }

    public function embed($url)
    {
        return Modify::value($url)->trackableEmbedUrl()->fetch();
    }
}
