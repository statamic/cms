<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FullUrlsTest extends TestCase
{
    #[Test]
    public function it_replaces_root_relative_urls_with_absolute_urls(): void
    {
        $domain = Site::current()->absoluteUrl();

        $modified = $this->modify('I had this totally <a href="/dreams/spiders-with-ramen-legs">crazy dream</a> last night and I know you want to hear all about it!');
        $expected = 'I had this totally <a href="'.$domain.'/dreams/spiders-with-ramen-legs">crazy dream</a> last night and I know you want to hear all about it!';
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->fullUrls()->fetch();
    }
}
