<?php

namespace Tests\Fixtures;

class TestManifest
{
    public $manifest;

    public function __construct()
    {
        $this->manifest = [
            'Bar' => [
                'id' => 'Bar',
                'package' => 'foo/bar',
                'name' => 'The Bar',
                'description' => 'The Bar addon for tests',
                'namespace' => 'Foo\Bar',
                'directory' => realpath(__DIR__.'/../fixtures/Addon'),
                'autoload' => 'src',
                'url' => 'http://foo.com/addons/bar',
                'developer' => 'John Foo',
                'developerUrl' => 'http://foo.com',
                'email' => 'john@foo.com',
                'version' => '1.0',
            ],
        ];
    }

    public function addons()
    {
        return collect($this->manifest);
    }
}
