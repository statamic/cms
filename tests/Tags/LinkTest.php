<?php

namespace Tests\Tags;

use Statamic\Facades\Data;
use Statamic\Facades\Parse;
use Tests\TestCase;

class LinkTest extends TestCase
{
    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    /** @test */
    public function it_outputs_datas_url_if_not_providing_a_url()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('en')->andReturnSelf();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ link:123 }}'));
        $this->assertEquals('/test', $this->tag('{{ link:123 absolute="false" }}'));
    }

    /** @test */
    public function it_outputs_datas_url_for_a_specific_site_if_not_providing_a_url()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('url')->andReturn('/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('/test', $this->tag('{{ link:123 in="fr" }}'));
        $this->assertEquals('/test', $this->tag('{{ link:123 in="fr" absolute="false" }}'));
    }

    /** @test */
    public function it_outputs_datas_absolute_url_if_not_providing_a_url()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('en')->andReturnSelf();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ link:123 absolute="true" }}'));
    }

    /** @test */
    public function it_outputs_datas_absolute_url_for_a_specific_site_if_not_providing_a_url()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('in')->with('fr')->andReturnSelf();
        $entry->shouldReceive('absoluteUrl')->andReturn('http://example.com/test');

        Data::shouldReceive('find')->with('123')->andReturn($entry);

        $this->assertEquals('http://example.com/test', $this->tag('{{ link:123 in="fr" absolute="true" }}'));
    }

    /** @test */
    public function it_outputs_nothing_if_data_doesnt_exist()
    {
        Data::shouldReceive('find')->with('123')->andReturnNull();

        $this->assertEquals('', $this->tag('{{ link:123 }}'));
    }
}
