<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Tests\TestCase;

class PathTest extends TestCase
{
    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    private function setSiteUrl($url)
    {
        Site::setConfig(['sites' => ['en' => ['url' => $url]]]);
    }

    /** @test */
    public function it_outputs_path_from_context_if_no_parameter_is_passed()
    {
        $this->assertEquals('the/path', $this->tag('{{ path }}', ['path' => 'the/path']));
    }

    /** @test */
    public function it_outputs_a_relative_url_when_site_url_is_relative()
    {
        $this->setSiteUrl('/');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" }}'));
    }

    /** @test */
    public function it_outputs_an_absolute_url_when_site_url_is_relative_and_absolute_param_is_true()
    {
        $this->setSiteUrl('/');
        $this->assertEquals('http://localhost/the/path', $this->tag('{{ path to="the/path" absolute="true" }}'));
        $this->assertEquals('http://localhost/the/path', $this->tag('{{ path to="/the/path" absolute="true" }}'));
    }

    /** @test */
    public function it_outputs_a_relative_url_when_site_url_is_relative_and_absolute_param_is_false()
    {
        $this->setSiteUrl('/');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" absolute="false" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" absolute="false" }}'));
    }

    /** @test */
    public function it_outputs_a_relative_url_when_site_url_is_absolute()
    {
        $this->setSiteUrl('http://example.com');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" }}'));
    }

    /** @test */
    public function it_outputs_an_absolute_url_when_site_url_is_absolute_and_absolute_param_is_true()
    {
        $this->setSiteUrl('http://example.com');
        $this->assertEquals('http://example.com/the/path', $this->tag('{{ path to="the/path" absolute="true" }}'));
        $this->assertEquals('http://example.com/the/path', $this->tag('{{ path to="/the/path" absolute="true" }}'));
    }

    /** @test */
    public function it_outputs_a_relative_url_when_site_url_is_absolute_and_absolute_param_is_false()
    {
        $this->setSiteUrl('http://example.com');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" absolute="false" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" absolute="false" }}'));
    }

    /** @test */
    public function it_outputs_a_relative_url_when_site_url_is_absolute_with_trailing_slash()
    {
        $this->setSiteUrl('http://example.com/');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" }}'));
    }

    /** @test */
    public function it_outputs_an_absolute_url_when_site_url_is_absolute_with_trailing_slash_and_absolute_param_is_true()
    {
        $this->setSiteUrl('http://example.com/');
        $this->assertEquals('http://example.com/the/path', $this->tag('{{ path to="the/path" absolute="true" }}'));
        $this->assertEquals('http://example.com/the/path', $this->tag('{{ path to="/the/path" absolute="true" }}'));
    }

    /** @test */
    public function it_outputs_a_relative_url_when_site_url_is_absolute_with_trailing_slash_and_absolute_param_is_false()
    {
        $this->setSiteUrl('http://example.com/');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" absolute="false" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" absolute="false" }}'));
    }
}
