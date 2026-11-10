<?php

namespace Tests\Routing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Routing\UrlBuilder;
use Statamic\Facades\Antlers;
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
    public function it_builds_placeholder_routes_without_antlers()
    {
        Antlers::shouldReceive('parse')->never();

        $this->assertEquals('/blog/post', $this->builder->build('/blog/{slug}'));
        $this->assertEquals('/blog/2015/01/02/post', $this->builder->build('/blog/{year}/{month}/{ day }/{slug}'));
        $this->assertEquals('/blog/foo/bar/post.html', $this->builder->build('blog/{slashed}/{slug}.html/'));
        $this->assertEquals('/test/bar-baz', $this->builder->merge(['foo' => 'Bar baz', 'number' => 5])->build('/test/{foo}'));
        $this->assertEquals('/test/5', $this->builder->build('/test/{number}'));
        $this->assertEquals('/blog/test', $this->builder->build('/blog/test'));
    }

    #[Test]
    #[DataProvider('routesNeedingAntlersProvider')]
    public function it_uses_antlers_when_a_route_is_more_than_placeholders($route, $merge)
    {
        $expected = Antlers::parse(
            preg_replace('/{\s*([a-zA-Z0-9_\-]+)\s*}/', '{{ $1 }}', $route),
            array_merge($this->entry->routeData(), $merge)
        );

        Antlers::partialMock()->shouldReceive('parse')->once()->passthru();

        $this->builder->merge($merge)->build($route);

        $this->assertNotNull($expected);
    }

    public static function routesNeedingAntlersProvider()
    {
        return [
            'written in antlers' => ['/blog/{{ slug }}', []],
            'missing value' => ['/blog/{missing}/{slug}', []],
            'null value' => ['/blog/{nothing}/{slug}', ['nothing' => null]],
            'array value' => ['/blog/{list_of}/{slug}', ['list_of' => ['a', 'b']]],
            'boolean value' => ['/blog/{flag}/{slug}', ['flag' => true]],
            'float value' => ['/blog/{price}/{slug}', ['price' => 1.5]],
            'value with braces' => ['/blog/{foo}/{slug}', ['foo' => 'a{slug}b']],
            'value with antlers' => ['/blog/{foo}/{slug}', ['foo' => '{{ slug }}']],
            'hyphenated name' => ['/blog/{my-field}/{slug}', ['my-field' => 'x']],
            'name starting with a digit' => ['/blog/{2nd}/{slug}', ['2nd' => 'x']],
            'antlers keyword' => ['/blog/{true}/{slug}', ['true' => 'x']],
            'leftover braces' => ['/blog/{ not a placeholder }/{slug}', []],
        ];
    }

    #[Test]
    public function placeholder_routes_give_the_same_url_as_antlers_for_names_antlers_knows()
    {
        // A value in the route data wins over a tag or modifier of the same name in Antlers,
        // but not over the language's own words. Either way a placeholder has to give
        // whatever Antlers gives, including when Antlers can't parse it.
        $words = [
            'true', 'TRUE', 'false', 'null', 'Null', 'void', 'list', 'as', 'and', 'And', 'or', 'xor', 'not',
            'if', 'elseif', 'else', 'unless', 'elseunless', 'endif', 'endunless', 'switch', 'once', 'endonce',
            'noparse', 'endnoparse', 'stop', 'recursive', 'new', 'bwa', 'bwo', 'bxor', 'bnot', 'bsl', 'bsr',
            'arr', 'merge', 'pluck', 'where', 'take', 'skip', 'orderby', 'groupby', 'gt', 'gte', 'lt', 'lte', 'eq', 'ne',
            'in', 'is', 'isnt', 'view', 'this',
            'id', 'slug', 'title', 'year', 'month', 'day', 'mount', 'parent_uri', 'depth', 'collection', 'locale', 'site', 'url', 'uri',
        ];

        $names = collect(app('statamic.tags')->keys())
            ->merge(app('statamic.modifiers')->keys())
            ->merge($words)
            ->unique()
            ->filter(fn ($name) => preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name))
            ->values();

        $this->assertGreaterThan(300, $names->count());

        $build = function ($route) {
            try {
                return $this->builder->build($route);
            } catch (\Throwable $e) {
                return get_class($e);
            }
        };

        foreach ($names as $name) {
            $this->builder->merge([$name => 'Some Value']);

            $this->assertSame(
                $build('/x/{{ '.$name.' }}/{{ slug }}'),
                $build('/x/{'.$name.'}/{slug}'),
                "The route placeholder {{$name}} gave a different URL without Antlers."
            );
        }
    }

    #[Test]
    public function it_preserves_dots_in_url()
    {
        $this->assertEquals('/blog/post.html', $this->builder->build('/blog/{{ slug }}.html'));
        $this->assertEquals('/blog/post.aspx', $this->builder->build('/blog/{{ slug }}.aspx'));
        $this->assertEquals('/blog/post.foo.bar', $this->builder->build('/blog/{{ slug }}.foo.bar'));
    }
}
