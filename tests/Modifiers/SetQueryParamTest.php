<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SetQueryParamTest extends TestCase
{
    protected $baseUrl = 'https://www.google.com/search';
    protected $queryParam = ['q', 'test'];

    public function existingQueryParametersProvider()
    {
        return [
            ['?q=', '?q=statamic', ['q']],
            ['?q=test', '?q=statamic', $this->queryParam],
            ['?q=test#test', '?q=statamic#test', $this->queryParam],
            ['?q=test&sourceid=chrome', '?q=statamic&sourceid=chrome', $this->queryParam],
            ['?sourceid=chrome&q=test', '?sourceid=chrome&q=statamic', $this->queryParam],
        ];
    }

    /**
     * @test
     *
     * @dataProvider existingQueryParametersProvider
     */
    public function it_updates_an_existing_query_param($expected, $input, array $queryParam = [])
    {
        $this->assertSame(
            $this->baseUrl.$expected,
            $this->modify($this->baseUrl.$input, $queryParam)
        );
    }

    public function nonExistingQueryParametersProvider()
    {
        return [
            ['?q=', '', ['q']],
            ['?q=test', '', $this->queryParam],
            ['?q=test#test', '#test', $this->queryParam],
            ['?sourceid=chrome&q=test', '?sourceid=chrome', $this->queryParam],
        ];
    }

    /**
     * @test
     *
     * @dataProvider nonExistingQueryParametersProvider
     */
    public function it_adds_a_non_existant_query_param($expected, $input, array $queryParam = [])
    {
        $this->assertSame(
            $this->baseUrl.$expected,
            $this->modify($this->baseUrl.$input, $queryParam)
        );
    }

    /** @test */
    public function it_does_nothing_if_no_parameters_are_passed()
    {
        $this->assertSame($this->baseUrl, $this->modify($this->baseUrl));
    }

    private function modify(string $url, array $queryParam = null)
    {
        if (is_null($queryParam)) {
            return Modify::value($url)->setQueryParam()->fetch();
        }

        return Modify::value($url)->setQueryParam($queryParam)->fetch();
    }
}
