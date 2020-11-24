<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class RemoveQueryParamTest extends TestCase
{
    protected $baseUrl = 'https://www.google.com/search';
    protected $queryParamKey = 'q';

    /** @test */
    public function it_removes_an_existing_query_param()
    {
        // Removal of a simple query param
        $this->assertSame($this->baseUrl, $this->modify("{$this->baseUrl}?q", $this->queryParamKey));
        $this->assertSame($this->baseUrl, $this->modify("{$this->baseUrl}?q=statamic", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}#test", $this->modify("{$this->baseUrl}?q=statamic#test", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome", $this->modify("{$this->baseUrl}?q&sourceid=chrome", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome", $this->modify("{$this->baseUrl}?q=statamic&sourceid=chrome", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome", $this->modify("{$this->baseUrl}?sourceid=chrome&q=statamic", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2&z[0][0]=z", $this->modify("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2&x=test&z[0][0]=z", 'x'));
        $this->assertSame("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2&z[0][0]=z", $this->modify("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2&x=test&z[0][0]=z", ['x', true]));

        // Removal of an array query param WITH reordering
        $this->assertSame($this->baseUrl, $this->modify("{$this->baseUrl}?q[0]=statamic", ['q[0]', true]));
        $this->assertSame("{$this->baseUrl}?q[0]=test1", $this->modify("{$this->baseUrl}?q[0]=statamic&q[1]=test1", ['q[0]', true]));
        $this->assertSame("{$this->baseUrl}?q[0][0]=statamic&q[0][1]=test2", $this->modify("{$this->baseUrl}?q[0][0]=statamic&q[0][1]=test1&q[0][2]=test2", ['q[0][1]', true]));
        $this->assertSame("{$this->baseUrl}?q[0][test]=statamic&q[0][0]=test2", $this->modify("{$this->baseUrl}?q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2", ['q[0][0]', true]));
        $this->assertSame("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test2&z[0][0]=z", $this->modify("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2&z[0][0]=z", ['q[0][0]', true]));
        $this->assertSame("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test2&x=test&z[0][0]=z", $this->modify("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2&x=test&z[0][0]=z", ['q[0][0]', true]));

        // Removal of an array query param WITHOUT reordering
        $this->assertSame($this->baseUrl, $this->modify("{$this->baseUrl}?q[0]=statamic", 'q[0]'));
        $this->assertSame("{$this->baseUrl}?q[1]=test1", $this->modify("{$this->baseUrl}?q[0]=statamic&q[1]=test1", 'q[0]'));
        $this->assertSame("{$this->baseUrl}?q[0][0]=statamic&q[0][2]=test2", $this->modify("{$this->baseUrl}?q[0][0]=statamic&q[0][1]=test1&q[0][2]=test2", 'q[0][1]'));
        $this->assertSame("{$this->baseUrl}?q[0][test]=statamic&q[0][1]=test2", $this->modify("{$this->baseUrl}?q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2", 'q[0][0]'));
        $this->assertSame("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][1]=test2&z[0][0]=z", $this->modify("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2&z[0][0]=z", 'q[0][0]'));
        $this->assertSame("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][1]=test2&x=test&z[0][0]=z", $this->modify("{$this->baseUrl}?a[0]=a&b[0][0]=b&q[0][test]=statamic&q[0][0]=test1&q[0][1]=test2&x=test&z[0][0]=z", 'q[0][0]'));
    }

    /** @test */
    public function it_does_nothing_if_the_query_param_key_does_not_exist()
    {
        $this->assertSame($this->baseUrl, $this->modify($this->baseUrl, $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}#test", $this->modify("{$this->baseUrl}#test", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome", $this->modify("{$this->baseUrl}?sourceid=chrome", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}?q[0]=statamic", $this->modify("{$this->baseUrl}?q[0]=statamic", 'q[1]'));
        $this->assertSame("{$this->baseUrl}?q[1]=statamic", $this->modify("{$this->baseUrl}?q[1]=statamic", ['q[2]', true]));
    }

    /** @test */
    public function it_does_nothing_if_no_parameters_are_passed()
    {
        $this->assertSame($this->baseUrl, $this->modify($this->baseUrl));
    }

    private function modify(string $url, $queryParamKey = null)
    {
        if (is_null($queryParamKey)) {
            return Modify::value($url)->removeQueryParam()->fetch();
        }

        return Modify::value($url)->removeQueryParam($queryParamKey)->fetch();
    }
}
