<?php namespace Tests;

use Statamic\API\Config;

class ConfigTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        app()->instance('config', $config = new \Illuminate\Config\Repository([]));

        $config->set([
            'foo' => 'bar',
            'myscope' => ['baz' => 'qux'],
            'system' => [
                'locales' => [
                    'en' => ['name' => 'English'],
                    'fr' => ['name' => 'French'],
                    'de' => ['name' => 'German']
                ]
            ]
        ]);
    }

    public function testCanGetConfigVar()
    {
        $this->assertEquals('bar', Config::get('foo'));
        $this->assertEquals('fallback', Config::get('nonexistant_variable', 'fallback'));
    }

    public function testCanGetScopedVar()
    {
        $this->assertEquals('qux', Config::get('myscope.baz'));
    }

    public function testCanGetDefaultLocale()
    {
        $this->assertEquals('en', Config::getDefaultLocale());
    }

    public function testCanGetOtherLocales()
    {
        site_locale('en');

        $this->assertEquals(['fr', 'de'], Config::getOtherLocales());
    }
}
