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
        // Update of a simple query param
        $this->assertSame("{$this->baseUrl}?q=", $this->modify("{$this->baseUrl}?q=statamic", ['q']));
        $this->assertSame("{$this->baseUrl}?q=test", $this->modify("{$this->baseUrl}?q=statamic", $this->queryParam));
        $this->assertSame("{$this->baseUrl}?q=test#test", $this->modify("{$this->baseUrl}?q=statamic#test", $this->queryParam));
        $this->assertSame("{$this->baseUrl}?q=test&sourceid=chrome", $this->modify("{$this->baseUrl}?q=statamic&sourceid=chrome", $this->queryParam));
        $this->assertSame("{$this->baseUrl}?q=test&sourceid=chrome", $this->modify("{$this->baseUrl}?sourceid=chrome&q=statamic", $this->queryParam));

        // Update of an array query param
        $this->assertSame("{$this->baseUrl}?q[0]=test&q[1]=second&test=test", $this->modify("{$this->baseUrl}?q[]=statamic&q[]=second&test=test", ['q[0]', 'test']));
        $this->assertSame("{$this->baseUrl}?q[0]=test&q[1]=second", $this->modify("{$this->baseUrl}?q[]=statamic&q[]=second", ['q[0]', 'test']));
        $this->assertSame("{$this->baseUrl}?q[0]=test&q[1]=second", $this->modify("{$this->baseUrl}?q[0]=statamic&q[1]=second", ['q[0]', 'test']));
        $this->assertSame("{$this->baseUrl}?q[0][0][0]=test&q[0][0][1]=second", $this->modify("{$this->baseUrl}?q[0][0][0]=statamic&q[0][0][1]=second", ['q[0][0][0]', 'test']));
        $this->assertSame("{$this->baseUrl}?q[0]=test", $this->modify("{$this->baseUrl}?q[0][0][0]=statamic&q[0][0][1]=second", ['q[0]', 'test']));
    }

    /** @test */
    public function it_adds_a_non_existant_query_param()
    {
        // Addition of a simple query param
        $this->assertSame("{$this->baseUrl}?q=", $this->modify($this->baseUrl, ['q']));
        $this->assertSame("{$this->baseUrl}?q=test", $this->modify($this->baseUrl, $this->queryParam));
        $this->assertSame("{$this->baseUrl}?q=test#test", $this->modify("{$this->baseUrl}#test", $this->queryParam));
        $this->assertSame("{$this->baseUrl}?q=test&sourceid=chrome", $this->modify("{$this->baseUrl}?sourceid=chrome", $this->queryParam));

        // Addition of an array query param
        $this->assertSame("{$this->baseUrl}?q[0]=statamic&q[1]=second&q[2]=&test=test", $this->modify("{$this->baseUrl}?q[]=statamic&q[]=second&test=test", ['q[]']));
        $this->assertSame("{$this->baseUrl}?q[0]=statamic&q[1]=second&q[2]=", $this->modify("{$this->baseUrl}?q[]=statamic&q[]=second", ['q[]']));
        $this->assertSame("{$this->baseUrl}?q[0]=statamic&q[1]=second&q[2]=test", $this->modify("{$this->baseUrl}?q[]=statamic&q[]=second", ['q[]', 'test']));
        $this->assertSame("{$this->baseUrl}?q[0]=statamic&q[1]=second&q[2]=test", $this->modify("{$this->baseUrl}?q[0]=statamic&q[1]=second", ['q[]', 'test']));
        $this->assertSame("{$this->baseUrl}?q[0]=statamic&q[1]=second&q[test]=test", $this->modify("{$this->baseUrl}?q[0]=statamic&q[1]=second", ['q[test]', 'test']));
        $this->assertSame("{$this->baseUrl}?q[0]=statamic&q[1]=second&q[2][test]=test", $this->modify("{$this->baseUrl}?q[0]=statamic&q[1]=second", ['q[][test]', 'test']));
        $this->assertSame("{$this->baseUrl}?q[0]=statamic&q[test]=second&q[1][test]=test", $this->modify("{$this->baseUrl}?q[0]=statamic&q[test]=second", ['q[][test]', 'test']));
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
