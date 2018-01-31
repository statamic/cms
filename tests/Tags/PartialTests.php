<?php

namespace Tests\Tags;

use Statamic\API\File;

trait PartialTests
{
    abstract protected function partialTag($src, $params = '');

    /** @test */
    function gets_partials_from_views_directory()
    {
        File::shouldReceive('get')->with(resource_path('views/mypartial.antlers.html'))->andReturn('the partial content');

        $this->assertEquals('the partial content', $this->partialTag('mypartial'));
    }

    /** @test */
    function gets_partials_from_partials_subdirectory()
    {
        config(['statamic.theming.dedicated_view_directories' => true]);
        File::shouldReceive('get')->with(resource_path('partials/mypartial.antlers.html'))->andReturn('the partial content');

        $this->assertEquals('the partial content', $this->partialTag('mypartial'));
    }

    /** @test */
    function partials_can_contain_front_matter()
    {
        File::shouldReceive('get')->with(resource_path('views/mypartial.antlers.html'))
            ->andReturn("---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial')
        );
    }

    /** @test */
    function partials_can_pass_data_through_params()
    {
        File::shouldReceive('get')->with(resource_path('views/mypartial.antlers.html'))
            ->andReturn("the partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial', 'foo="bar"')
        );
    }

    /** @test */
    function parameter_will_override_partial_front_matter()
    {
        File::shouldReceive('get')->with(resource_path('views/mypartial.antlers.html'))
            ->andReturn("---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with baz',
            $this->partialTag('mypartial', 'foo="baz"')
        );
    }

    /** @test */
    function php_templates_should_be_parsed()
    {
        File::shouldReceive('get')->with(resource_path('views/mypartial.antlers.html'))->andReturnNull();
        File::shouldReceive('get')->with(resource_path('views/mypartial.antlers.php'))->andReturn('the partial content <?php echo "with some php"; ?>');

        $this->assertEquals('the partial content with some php', $this->partialTag('mypartial'));
    }
}