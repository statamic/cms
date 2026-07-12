<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class AddQueryParamTest extends TestCase
{
    protected $baseUrl = 'https://www.google.com/search';
    protected $queryParam = ['q', 'test'];

    #[Test]
    public function it_adds_a_new_query_param()
    {
        $this->assertSame("{$this->baseUrl}?q=", $this->modify($this->baseUrl, ['q']));
        $this->assertSame("{$this->baseUrl}?q=test", $this->modify($this->baseUrl, $this->queryParam));
        $this->assertSame("{$this->baseUrl}?sourceid=chrome&q=test", $this->modify("{$this->baseUrl}?sourceid=chrome", $this->queryParam));
        $this->assertSame("{$this->baseUrl}?q=test#test", $this->modify("{$this->baseUrl}#test", $this->queryParam));
    }

    #[Test]
    public function it_does_nothing_if_no_parameters_are_passed()
    {
        $this->assertSame($this->baseUrl, $this->modify($this->baseUrl));
        $this->assertSame("{$this->baseUrl}#test", $this->modify("{$this->baseUrl}#test"));
    }

    private function modify(string $url, ?array $queryParam = null)
    {
        if (is_null($queryParam)) {
            return Modify::value($url)->addQueryParam()->fetch();
        }

        return Modify::value($url)->addQueryParam($queryParam)->fetch();
    }
}
