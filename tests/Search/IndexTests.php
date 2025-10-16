<?php

namespace Tests\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Index;

trait IndexTests
{
    public function tearDown(): void
    {
        // Reset the static state of the Index class
        Index::resolveNameUsing(null);

        parent::tearDown();
    }

    abstract public function getIndexClass();

    public function getIndex($name, $config, $locale)
    {
        $class = $this->getIndexClass();

        return new $class($name, $config, $locale);
    }

    #[Test, DataProvider('nameProvider')]
    public function it_can_get_the_name($name, $config, $locale, $resolver, $expected)
    {
        if ($resolver) {
            $this->getIndexClass()::resolveNameUsing($resolver);
        }

        $index = $this->getIndex($name, $config, $locale);

        $this->assertEquals($expected, $index->name());
    }

    public static function nameProvider()
    {
        return [
            'basic' => ['test', [], null, null, 'test'],
            'with locale' => ['test', [], 'en', null, 'test_en'],
            'resolver' => ['test', [], null, fn ($name, $locale) => 'prefix_'.$name.'_'.$locale, 'prefix_test_'],
            'resolver with locale' => ['test', [], 'en', fn ($name, $locale) => 'prefix_'.$name.'_'.$locale, 'prefix_test_en'],
        ];
    }
}
