<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Data;
use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Tests\TestCase;

class PathTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr'],
        ]);
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    private function setSiteUrl($url)
    {
        $this->setSiteValue('en', 'url', $url);
    }

    #[Test]
    public function it_outputs_path_from_context_if_no_parameter_is_passed()
    {
        $this->assertEquals('the/path', $this->tag('{{ path }}', ['path' => 'the/path']));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_relative()
    {
        $this->setSiteUrl('/');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_relative_with_subdirectory()
    {
        $this->setSiteUrl('/sub');
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="the/path" }}'));
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="/the/path" }}'));
    }

    #[Test]
    public function it_outputs_an_absolute_url_when_site_url_is_relative_and_absolute_param_is_true()
    {
        $this->setSiteUrl('/');
        $this->assertEquals('http://localhost/the/path', $this->tag('{{ path to="the/path" absolute="true" }}'));
        $this->assertEquals('http://localhost/the/path', $this->tag('{{ path to="/the/path" absolute="true" }}'));
    }

    #[Test]
    public function it_outputs_an_absolute_url_when_site_url_is_relative_with_subdirectory_and_absolute_param_is_true()
    {
        $this->setSiteUrl('/sub');
        $this->assertEquals('http://localhost/sub/the/path', $this->tag('{{ path to="the/path" absolute="true" }}'));
        $this->assertEquals('http://localhost/sub/the/path', $this->tag('{{ path to="/the/path" absolute="true" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_relative_and_absolute_param_is_false()
    {
        $this->setSiteUrl('/');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" absolute="false" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_relative_with_subdirectory_and_absolute_param_is_false()
    {
        $this->setSiteUrl('/sub');
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="the/path" absolute="false" }}'));
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="/the/path" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_absolute()
    {
        $this->setSiteUrl('http://example.com');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_absolute_with_subdirectory()
    {
        $this->setSiteUrl('http://example.com/sub/');
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="the/path" }}'));
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="/the/path" }}'));
    }

    #[Test]
    public function it_outputs_an_absolute_url_when_site_url_is_absolute_and_absolute_param_is_true()
    {
        $this->setSiteUrl('http://example.com');
        $this->assertEquals('http://example.com/the/path', $this->tag('{{ path to="the/path" absolute="true" }}'));
        $this->assertEquals('http://example.com/the/path', $this->tag('{{ path to="/the/path" absolute="true" }}'));
    }

    #[Test]
    public function it_outputs_an_absolute_url_when_site_url_is_absolute_with_subdirectory_and_absolute_param_is_true()
    {
        $this->setSiteUrl('http://example.com/sub');
        $this->assertEquals('http://example.com/sub/the/path', $this->tag('{{ path to="the/path" absolute="true" }}'));
        $this->assertEquals('http://example.com/sub/the/path', $this->tag('{{ path to="/the/path" absolute="true" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_absolute_and_absolute_param_is_false()
    {
        $this->setSiteUrl('http://example.com');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" absolute="false" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_absolute_with_subdirectory_and_absolute_param_is_false()
    {
        $this->setSiteUrl('http://example.com/sub');
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="the/path" absolute="false" }}'));
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="/the/path" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_absolute_with_trailing_slash()
    {
        $this->setSiteUrl('http://example.com/');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_absolute_with_subdirectory_and_trailing_slash()
    {
        $this->setSiteUrl('http://example.com/sub/');
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="the/path" }}'));
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="/the/path" }}'));
    }

    #[Test]
    public function it_outputs_an_absolute_url_when_site_url_is_absolute_with_trailing_slash_and_absolute_param_is_true()
    {
        $this->setSiteUrl('http://example.com/');
        $this->assertEquals('http://example.com/the/path', $this->tag('{{ path to="the/path" absolute="true" }}'));
        $this->assertEquals('http://example.com/the/path', $this->tag('{{ path to="/the/path" absolute="true" }}'));
    }

    #[Test]
    public function it_outputs_an_absolute_url_when_site_url_is_absolute_with_subdirectory_trailing_slash_and_absolute_param_is_true()
    {
        $this->setSiteUrl('http://example.com/sub/');
        $this->assertEquals('http://example.com/sub/the/path', $this->tag('{{ path to="the/path" absolute="true" }}'));
        $this->assertEquals('http://example.com/sub/the/path', $this->tag('{{ path to="/the/path" absolute="true" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_absolute_with_trailing_slash_and_absolute_param_is_false()
    {
        $this->setSiteUrl('http://example.com/');
        $this->assertEquals('/the/path', $this->tag('{{ path to="the/path" absolute="false" }}'));
        $this->assertEquals('/the/path', $this->tag('{{ path to="/the/path" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_a_relative_url_when_site_url_is_absolute_with_subdirectory_and_trailing_slash_and_absolute_param_is_false()
    {
        $this->setSiteUrl('http://example.com/sub/');
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="the/path" absolute="false" }}'));
        $this->assertEquals('/sub/the/path', $this->tag('{{ path to="/the/path" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_datas_url()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('en')->andReturnSelf();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ path id="123" }}'));
        $this->assertEquals('/test', $this->tag('{{ path id="123" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_datas_url_for_a_specific_site()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ path id="123" in="fr" }}'));
        $this->assertEquals('/test', $this->tag('{{ path id="123" in="fr" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_datas_url_for_the_current_site()
    {
        Site::setCurrent('fr');

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ path id="123" }}'));
        $this->assertEquals('/test', $this->tag('{{ path id="123" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_datas_url_for_the_original_site_if_it_doesnt_exist_in_the_current_one()
    {
        Site::setCurrent('fr');

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnNull();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ path id="123" }}'));
        $this->assertEquals('/test', $this->tag('{{ path id="123" absolute="false" }}'));
    }

    #[Test]
    public function it_outputs_nothing_if_it_doesnt_exist_in_the_requested_site()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnNull();

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('', $this->tag('{{ path id="123" in="fr" }}'));
    }

    #[Test]
    public function it_outputs_datas_absolute_url()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('en')->andReturnSelf();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ path id="123" absolute="true" }}'));
    }

    #[Test]
    public function it_outputs_datas_absolute_url_for_a_specific_site()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ path id="123" in="fr" absolute="true" }}'));
    }

    #[Test]
    public function it_outputs_datas_absolute_url_for_the_current_site()
    {
        Site::setCurrent('fr');

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ path id="123" absolute="true" }}'));
    }

    #[Test]
    public function it_outputs_datas_absolute_url_for_the_original_site_if_it_doesnt_exist_in_the_current_one()
    {
        Site::setCurrent('fr');

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnNull();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ path id="123" absolute="true" }}'));
    }
}
