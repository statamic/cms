<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class RemoveQueryParamTest extends TestCase
{
    protected $baseUrl = 'https://www.google.com/search';
    protected $queryParamKey = 'q';

    #[Test]
    public function it_removes_an_existing_query_param()
    {
        $this->assertSame($this->baseUrl, $this->modify("{$this->baseUrl}?q=statamic", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}#test", $this->modify("{$this->baseUrl}?q=statamic#test", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome", $this->modify("{$this->baseUrl}?q=statamic&sourceid=chrome", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome", $this->modify("{$this->baseUrl}?sourceid=chrome&q=statamic", $this->queryParamKey));
    }

    #[Test]
    public function it_does_nothing_if_the_query_param_key_does_not_exist()
    {
        $this->assertSame($this->baseUrl, $this->modify($this->baseUrl, $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}#test", $this->modify("{$this->baseUrl}#test", $this->queryParamKey));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome", $this->modify("{$this->baseUrl}?sourceid=chrome", $this->queryParamKey));
    }

    #[Test]
    public function it_does_nothing_if_no_parameters_are_passed()
    {
        $this->assertSame($this->baseUrl, $this->modify($this->baseUrl));
    }

    private function modify(string $url, ?string $queryParamKey = null)
    {
        if (is_null($queryParamKey)) {
            return Modify::value($url)->removeQueryParam()->fetch();
        }

        return Modify::value($url)->removeQueryParam($queryParamKey)->fetch();
    }
}
