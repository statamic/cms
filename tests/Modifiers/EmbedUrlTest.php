<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class EmbedUrlTest extends TestCase
{
    /** @test */
    public function it_leaves_urls_from_unknown_providers_untouched()
    {
        $this->assertEquals('https://statamic.com/video/hello', $this->embed('https://statamic.com/video/hello'));
    }

    /** @test */
    public function it_transforms_vimeo_urls()
    {
        $embedUrl = 'https://player.vimeo.com/video/71360261?dnt=1';

        $this->assertEquals($embedUrl, $this->embed('https://vimeo.com/71360261'));

        $this->assertEquals(
            $embedUrl.'&foo=bar',
            $this->embed('https://vimeo.com/71360261?foo=bar'),
            'It appends the do not track query param if a query string already exists.'
        );
    }

    /** @test */
    public function it_transforms_private_vimeo_urls()
    {
        $embedUrl = 'https://player.vimeo.com/video/735352648?dnt=1&h=fa55a4d0fc';

        $this->assertEquals($embedUrl, $this->embed('https://vimeo.com/735352648/fa55a4d0fc'));

        $this->assertEquals(
            $embedUrl.'&foo=bar',
            $this->embed('https://vimeo.com/735352648/fa55a4d0fc?foo=bar'),
            'It appends the do not track query param if a query string already exists.'
        );
    }

    /** @test */
    public function it_transforms_youtube_urls()
    {
        $embedUrl = 'https://www.youtube-nocookie.com/embed/s72r_wu_NVY';

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

        $this->assertEquals(
            'https://www.youtube-nocookie.com/embed/hyJ7CBs_2RQ?start=2',
            $this->embed('https://www.youtube.com/watch?v=hyJ7CBs_2RQ&t=2'),
            'It transforms the start time parameter of full youtube links'
        );
    }

    public function embed($url)
    {
        return Modify::value($url)->embedUrl()->fetch();
    }
}
