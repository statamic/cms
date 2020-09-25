<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Tests\FakesViews;
use Tests\TestCase;

class PartialTagsTest extends TestCase
{
    use FakesViews;

    public function setUp(): void
    {
        parent::setUp();
        $this->withFakeViews();
        $this->markTestIncomplete();
    }

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    protected function partialTag($src, $params = '')
    {
        return $this->tag("{{ partial:{$src} $params }}");
    }

    /** @test */
    public function gets_partials_from_views_directory()
    {
        $this->viewShouldReturnRaw('mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('mypartial'));
    }

    /** @test */
    public function gets_partials_from_partials_directory()
    {
        $this->viewShouldReturnRaw('partials.sub.mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    /** @test */
    public function gets_partials_with_underscore_prefix()
    {
        $this->viewShouldReturnRaw('sub._mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    /** @test */
    public function partials_can_contain_front_matter()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial')
        );
    }

    /** @test */
    public function partials_can_pass_data_through_params()
    {
        $this->viewShouldReturnRaw('mypartial', 'the partial content with {{ foo }}');

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial', 'foo="bar"')
        );
    }

    public function partials_have_slots_when_used_as_pair()
    {
        $this->viewShouldReturnRaw('mypartial', '{{ slot }}');

        $this->tag('{{ partial:mypartial }}outside{{ /partial:mypartial }}');

        $this->assertEquals(
            'outside',
            $this->partialTag('mypartial')
        );
    }

    /** @test */
    public function parameter_will_override_partial_front_matter()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with baz',
            $this->partialTag('mypartial', 'foo="baz"')
        );
    }
}
