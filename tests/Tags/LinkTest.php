<?php

namespace Tests\Tags;

use Statamic\Facades\Data;
use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Tests\TestCase;

class LinkTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Site::setConfig(['sites' => [
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr'],
        ]]);
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    /** @test */
    public function it_outputs_datas_url()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('en')->andReturnSelf();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ link:123 }}'));
        $this->assertEquals('/test', $this->tag('{{ link:123 absolute="false" }}'));
    }

    /** @test */
    public function it_outputs_datas_url_for_a_specific_site()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ link:123 in="fr" }}'));
        $this->assertEquals('/test', $this->tag('{{ link:123 in="fr" absolute="false" }}'));
    }

    /** @test */
    public function it_outputs_datas_url_for_the_current_site()
    {
        Site::setCurrent('fr');

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ link:123 }}'));
        $this->assertEquals('/test', $this->tag('{{ link:123 absolute="false" }}'));
    }

    /** @test */
    public function it_outputs_datas_url_for_the_original_site_if_it_doesnt_exist_in_the_current_one()
    {
        Site::setCurrent('fr');

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnNull();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ link:123 }}'));
        $this->assertEquals('/test', $this->tag('{{ link:123 absolute="false" }}'));
    }

    /** @test */
    public function it_outputs_nothing_if_it_doesnt_exist_in_the_requested_site()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnNull();

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('', $this->tag('{{ link:123 in="fr" }}'));
    }

    /** @test */
    public function it_outputs_datas_absolute_url()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('en')->andReturnSelf();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ link:123 absolute="true" }}'));
    }

    /** @test */
    public function it_outputs_datas_absolute_url_for_a_specific_site()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ link:123 in="fr" absolute="true" }}'));
    }

    /** @test */
    public function it_outputs_datas_absolute_url_for_the_current_site()
    {
        Site::setCurrent('fr');

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ link:123 absolute="true" }}'));
    }

    /** @test */
    public function it_outputs_datas_absolute_url_for_the_original_site_if_it_doesnt_exist_in_the_current_one()
    {
        Site::setCurrent('fr');

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnNull();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ link:123 absolute="true" }}'));
    }

    /** @test */
    public function it_outputs_nothing_if_data_doesnt_exist()
    {
        Data::shouldReceive('find')->with('123')->andReturnNull();

        $this->assertEquals('', $this->tag('{{ link:123 }}'));
    }
}
