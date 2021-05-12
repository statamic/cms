<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SetQueryParamTest extends TestCase
{
    protected $baseUrl = 'https://www.google.com/search';
    protected $queryParam = ['q', 'test'];

    /** @test */
    public function it_updates_an_existing_query_param()
    {
        $this->assertSame("{$this->baseUrl}?q=", $this->modify("{$this->baseUrl}?q=statamic", ['q']));
        $this->assertSame("{$this->baseUrl}?q=test", $this->modify("{$this->baseUrl}?q=statamic", $this->queryParam));
        $this->assertSame("{$this->baseUrl}?q=test#test", $this->modify("{$this->baseUrl}?q=statamic#test", $this->queryParam));
        $this->assertSame("{$this->baseUrl}?q=test&sourceid=chrome", $this->modify("{$this->baseUrl}?q=statamic&sourceid=chrome", $this->queryParam));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome&q=test", $this->modify("{$this->baseUrl}?sourceid=chrome&q=statamic", $this->queryParam));
    }

    /** @test */
    public function it_adds_a_non_existant_query_param()
    {
        $this->assertSame("{$this->baseUrl}?q=", $this->modify($this->baseUrl, ['q']));
        $this->assertSame("{$this->baseUrl}?q=test", $this->modify($this->baseUrl, $this->queryParam));
        $this->assertSame("{$this->baseUrl}?q=test#test", $this->modify("{$this->baseUrl}#test", $this->queryParam));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome&q=test", $this->modify("{$this->baseUrl}?sourceid=chrome", $this->queryParam));
    }

    /** @test */
    public function it_does_nothing_if_no_parameters_are_passed()
    {
        $this->assertSame($this->baseUrl, $this->modify($this->baseUrl));
    }

    private function modify(string $url, ?array $queryParam = null)
    {
        if (is_null($queryParam)) {
            return Modify::value($url)->setQueryParam()->fetch();
        }

        return Modify::value($url)->setQueryParam($queryParam)->fetch();
    }
}
