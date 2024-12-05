<?php

namespace Tests\Routing;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Routing\UrlBuilder;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UrlBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /**
     * @var \Statamic\Contracts\Routing\UrlBuilder
     */
    protected $builder;

    /**
     * @var \Statamic\Entries\File\Entry
     */
    protected $entry;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        $entry = tap(\Statamic\Facades\Entry::make()
            ->id('post')
            ->locale('en')
            ->collection(
                tap(\Statamic\Facades\Collection::make('example')->dated(true))->save()
            )
            ->slug('post')
            ->date('2015-01-02')
            ->data(['foo' => 'bar', 'slashed' => 'foo/bar'])
        )->save();

        $entry->makeLocalization('fr')->slug('le-post')->save();

        $this->builder = app(UrlBuilder::class)->content($entry);
        $this->entry = $entry;
    }

    #[Test]
    public function it_builds_a_simple_url()
    {
        $this->assertEquals('/blog/post', $this->builder->build('/blog/{{ slug }}'));
    }

    #[Test]
    public function it_builds_a_simple_url_using_mustache_tags()
    {
        $this->assertEquals('/blog/post', $this->builder->build('/blog/{slug}'));
    }

    #[Test]
    public function it_builds_a_date_url()
    {
        $this->assertEquals('/blog/2015/01/02/post', $this->builder->build('/blog/{{ year }}/{{ month }}/{{ day }}/{{ slug }}'));
        $this->assertEquals('/blog/1420156800/post', $this->builder->build('/blog/{{ date format="U" }}/{{ slug }}'));
        $this->assertEquals('/blog/2-jan-15/post', $this->builder->build('/blog/{{ date format="j-M-y" }}/{{ slug }}'));
    }

    #[Test]
    public function it_builds_a_simple_localized_url()
    {
        $this->builder->content($this->entry->in('fr'));
        $this->assertEquals('/blog/le-post', $this->builder->build('/blog/{{ slug }}'));
    }

    #[Test]
    public function it_keeps_slashes_in_values()
    {
        $this->builder->content($this->entry);
        $this->assertEquals('/blog/foo/bar', $this->builder->build('/blog/{{ slashed }}'));
    }

    #[Test]
    public function it_trims_trailing_slashes()
    {
        $this->assertEquals('/blog/test', $this->builder->build('/blog/test/'));
    }

    #[Test]
    public function it_ensures_a_leading_slash()
    {
        $this->assertEquals('/blog/test', $this->builder->build('blog/test'));
    }

    #[Test]
    public function it_merges_in_extra_variables()
    {
        $this->assertEquals(
            '/bar/post',
            $this->builder->build('/{{ foo }}/{{ slug }}')
        );

        $this->assertEquals(
            '/baz/post',
            $this->builder->merge(['foo' => 'baz'])->build('/{{ foo }}/{{ slug }}')
        );
    }

    #[Test]
    public function it_slugifies_non_slugified_values()
    {
        $this->assertEquals(
            '/test/bar-baz',
            $this->builder->merge(['foo' => 'Bar baz'])->build('/test/{{ foo }}')
        );

        $this->assertEquals(
            '/test/bar_baz',
            $this->builder->merge(['foo' => 'bar_baz'])->build('/test/{{ foo }}')
        );
    }

    #[Test]
    public function it_removes_consecutive_slashes_left_by_null_values()
    {
        $this->assertEquals(
            '/test/foo/baz',
            $this->builder->merge(['foo' => 'foo', 'baz' => 'baz'])->build('/test/{{ foo }}/{{ bar }}/{{ baz }}')
        );
    }

    #[Test]
    public function it_preserves_dots_in_url()
    {
        $this->assertEquals('/blog/post.html', $this->builder->build('/blog/{{ slug }}.html'));
        $this->assertEquals('/blog/post.aspx', $this->builder->build('/blog/{{ slug }}.aspx'));
        $this->assertEquals('/blog/post.foo.bar', $this->builder->build('/blog/{{ slug }}.foo.bar'));
    }
}
