<?php

namespace Tests\Antlers\Runtime\Libraries;

use Tests\Antlers\ParserTestCase;

class PathLibraryTest extends ParserTestCase
{
    protected $testPaths = [
        'path',
        'null',
        null,
        'null',
        '',
        'nested/path',
        'nested/path/',
    ];

    public function test_resource_equivalency()
    {
        foreach ($this->testPaths as $path) {
            $this->assertSame(
                resource_path($path),
                $this->renderLibraryMethod('path.resource(filePath)', [
                    'filePath' => $path,
                ]),
                'path.resource: '.$path
            );
        }
    }

    public function test_storage_equivalency()
    {
        foreach ($this->testPaths as $path) {
            $this->assertSame(
                storage_path($path),
                $this->renderLibraryMethod('path.storage(filePath)', [
                    'filePath' => $path,
                ]),
                'path.storage: '.$path
            );
        }
    }

    public function test_app_equivalency()
    {
        foreach ($this->testPaths as $path) {
            $this->assertSame(
                app_path($path),
                $this->renderLibraryMethod('path.app(filePath)', [
                    'filePath' => $path,
                ]),
                'path.app: '.$path
            );
        }
    }

    public function test_base_equivalency()
    {
        foreach ($this->testPaths as $path) {
            $this->assertSame(
                base_path($path),
                $this->renderLibraryMethod('path.base(filePath)', [
                    'filePath' => $path,
                ]),
                'path.base: '.$path
            );
        }
    }

    public function test_config_equivalency()
    {
        foreach ($this->testPaths as $path) {
            $this->assertSame(
                config_path($path),
                $this->renderLibraryMethod('path.config(filePath)', [
                    'filePath' => $path,
                ]),
                'path.config: '.$path
            );
        }
    }

    public function test_database_equivalency()
    {
        foreach ($this->testPaths as $path) {
            $this->assertSame(
                database_path($path),
                $this->renderLibraryMethod('path.database(filePath)', [
                    'filePath' => $path,
                ]),
                'path.database: '.$path
            );
        }
    }

    public function test_public_equivalency()
    {
        foreach ($this->testPaths as $path) {
            $this->assertSame(
                public_path($path),
                $this->renderLibraryMethod('path.public(filePath)', [
                    'filePath' => $path,
                ]),
                'path.public: '.$path
            );
        }
    }

    public function test_statamic_equivalency()
    {
        foreach ($this->testPaths as $path) {
            $this->assertSame(
                statamic_path($path),
                $this->renderLibraryMethod('path.statamic(filePath)', [
                    'filePath' => $path,
                ]),
                'path.statamic: '.$path
            );
        }
    }
}
