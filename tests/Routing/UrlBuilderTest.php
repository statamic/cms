<?php

namespace Tests\Routing;

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

        $entry = \Statamic\Facades\Entry::make()
            ->id('post')
            ->locale('en')
            ->collection(
                tap(\Statamic\Facades\Collection::make('example')->dated(true))->save()
            )
            ->slug('post')
            ->date('2015-01-02')
            ->data(['foo' => 'bar', 'slashed' => 'foo/bar']);

        $entry->addLocalization(
            $entry->makeLocalization('fr')->slug('le-post')
        );

        $this->builder = app(UrlBuilder::class)->content($entry);
        $this->entry = $entry;
    }

    /** @test */
    public function it_builds_a_simple_url()
    {
        $this->assertEquals('/blog/post', $this->builder->build('/blog/{{ slug }}'));
    }

    /** @test */
    public function it_builds_a_simple_url_using_mustache_tags()
    {
        $this->assertEquals('/blog/post', $this->builder->build('/blog/{slug}'));
    }

    /** @test */
    public function it_builds_a_date_url()
    {
        $this->assertEquals('/blog/2015/01/02/post', $this->builder->build('/blog/{{ year }}/{{ month }}/{{ day }}/{{ slug }}'));
    }

    /** @test */
    public function it_builds_a_simple_localized_url()
    {
        $this->builder->content($this->entry->in('fr'));
        $this->assertEquals('/blog/le-post', $this->builder->build('/blog/{{ slug }}'));
    }

    /** @test */
    public function it_keeps_slashes_in_values()
    {
        $this->builder->content($this->entry);
        $this->assertEquals('/blog/foo/bar', $this->builder->build('/blog/{{ slashed }}'));
    }

    /** @test */
    public function it_trims_trailing_slashes()
    {
        $this->assertEquals('/blog/test', $this->builder->build('/blog/test/'));
    }

    /** @test */
    public function it_ensures_a_leading_slash()
    {
        $this->assertEquals('/blog/test', $this->builder->build('blog/test'));
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_removes_consecutive_slashes_left_by_null_values()
    {
        $this->assertEquals(
            '/test/foo/baz',
            $this->builder->merge(['foo' => 'foo', 'baz' => 'baz'])->build('/test/{{ foo }}/{{ bar }}/{{ baz }}')
        );
    }
}
