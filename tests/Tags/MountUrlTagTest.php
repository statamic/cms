<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MountUrlTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'english' => ['url' => 'http://example.com/', 'locale' => 'en'],
            'french' => ['url' => 'http://example.com/fr/', 'locale' => 'fr'],
            'german' => ['url' => 'http://example.de/', 'locale' => 'de'],
        ]);

        Collection::make('pages')->sites(['english', 'french', 'german'])->routes([
            'english' => 'pages/{slug}',
            'french' => 'le-pages/{slug}',
            'german' => 'der-pages/{slug}',
        ])->save();

        $mountEn = EntryFactory::collection('pages')->slug('blog')->locale('english')->id('blog-en')->create();
        $mountFr = EntryFactory::collection('pages')->slug('le-blog')->locale('french')->origin('blog-en')->id('blog-fr')->create();
        $mountDe = EntryFactory::collection('pages')->slug('der-blog')->locale('german')->origin('blog-en')->id('blog-de')->create();

        Collection::make('blog')->routes('{mount}/{slug}')->mount($mountEn->id())->save();
    }

    #[Test]
    #[DataProvider('mountProvider')]
    public function it_gets_url($currentSite, $template, $expected)
    {
        Site::setCurrent($currentSite);
        $this->assertParseEquals($expected, $template);
    }

    public static function mountProvider()
    {
        return [
            ['english', '{{ mount_url:blog }}', '/pages/blog'],
            ['english', '{{ mount_url:blog site="english" }}', '/pages/blog'],
            ['english', '{{ mount_url:blog site="french" }}', '/fr/le-pages/le-blog'],
            ['english', '{{ mount_url:blog site="german" }}', 'http://example.de/der-pages/der-blog'],
            ['french', '{{ mount_url:blog }}', '/fr/le-pages/le-blog'],
            ['french', '{{ mount_url:blog site="english" }}', '/pages/blog'],
            ['french', '{{ mount_url:blog site="french" }}', '/fr/le-pages/le-blog'],
            ['french', '{{ mount_url:blog site="german" }}', 'http://example.de/der-pages/der-blog'],
            ['german', '{{ mount_url:blog }}', '/der-pages/der-blog'],
            ['german', '{{ mount_url:blog site="english" }}', 'http://example.com/pages/blog'],
            ['german', '{{ mount_url:blog site="french" }}', 'http://example.com/fr/le-pages/le-blog'],
            ['german', '{{ mount_url:blog site="german" }}', '/der-pages/der-blog'],
            ['english', '{{ mount_url handle="blog" }}', '/pages/blog'],
            ['english', '{{ mount_url handle="blog" site="english" }}', '/pages/blog'],
            ['english', '{{ mount_url handle="blog" site="french" }}', '/fr/le-pages/le-blog'],
            ['english', '{{ mount_url handle="blog" site="german" }}', 'http://example.de/der-pages/der-blog'],
            ['french', '{{ mount_url handle="blog" }}', '/fr/le-pages/le-blog'],
            ['french', '{{ mount_url handle="blog" site="english" }}', '/pages/blog'],
            ['french', '{{ mount_url handle="blog" site="french" }}', '/fr/le-pages/le-blog'],
            ['french', '{{ mount_url handle="blog" site="german" }}', 'http://example.de/der-pages/der-blog'],
            ['german', '{{ mount_url handle="blog" }}', '/der-pages/der-blog'],
            ['german', '{{ mount_url handle="blog" site="english" }}', 'http://example.com/pages/blog'],
            ['german', '{{ mount_url handle="blog" site="french" }}', 'http://example.com/fr/le-pages/le-blog'],
            ['german', '{{ mount_url handle="blog" site="german" }}', '/der-pages/der-blog'],
        ];
    }

    private function assertParseEquals($expected, $template, $context = [])
    {
        $this->assertEquals($expected, (string) Antlers::parse($template, $context));
    }
}
