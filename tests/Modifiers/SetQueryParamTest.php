<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SetQueryParamTest extends TestCase
{
    protected $baseUrl = 'https://www.google.com/search';
    protected static $queryParam = ['q', 'test'];

    public static function existingQueryParametersProvider()
    {
        return [
            ['?q=', '?q=statamic', ['q']],
            ['?q=test', '?q=statamic', static::$queryParam],
            ['?q=test#test', '?q=statamic#test', static::$queryParam],
            ['?q=test&sourceid=chrome', '?q=statamic&sourceid=chrome', static::$queryParam],
            ['?sourceid=chrome&q=test', '?sourceid=chrome&q=statamic', static::$queryParam],
        ];
    }

    #[Test]
    #[DataProvider('existingQueryParametersProvider')]
    public function it_updates_an_existing_query_param($expected, $input, array $queryParam = [])
    {
        $this->assertSame(
            $this->baseUrl.$expected,
            $this->modify($this->baseUrl.$input, $queryParam)
        );
    }

    public static function nonExistingQueryParametersProvider()
    {
        return [
            ['?q=', '', ['q']],
            ['?q=test', '', static::$queryParam],
            ['?q=test#test', '#test', static::$queryParam],
            ['?sourceid=chrome&q=test', '?sourceid=chrome', static::$queryParam],
        ];
    }

    #[Test]
    #[DataProvider('nonExistingQueryParametersProvider')]
    public function it_adds_a_non_existant_query_param($expected, $input, array $queryParam = [])
    {
        $this->assertSame(
            $this->baseUrl.$expected,
            $this->modify($this->baseUrl.$input, $queryParam)
        );
    }

    #[Test]
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
