<?php

namespace Tests\StarterKits;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\YAML;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use Concerns\BacksUpComposerJson;

    protected $files;
    protected $configPath;
    protected $exportPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
        $this->configPath = base_path('starter-kit.yaml');
        $this->exportPath = base_path('../cool-runnings');

        if ($this->files->exists($this->configPath)) {
            $this->files->delete($this->configPath);
        }

        if ($this->files->exists($this->exportPath)) {
            $this->files->deleteDirectory($this->exportPath);
        }

        $this->restoreComposerJson();
        $this->backupComposerJson();
    }

    public function tearDown(): void
    {
        $this->restoreComposerJson();

        parent::tearDown();
    }

    /** @test */
    public function it_can_stub_out_a_new_config()
    {
        $this->assertFileNotExists($this->configPath);

        $this->exportCoolRunnings();

        $this->assertFileExists($this->configPath);
        $this->assertFileHasContent('# export_paths:', $this->configPath);
    }

    /** @test */
    public function it_can_export_files()
    {
        $this->setExportPaths([
            'config/filesystems.php',
            'resources/views/welcome.blade.php',
        ]);

        $this->assertFileNotExists($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileNotExists($composerJson = $this->exportPath('resources/views/welcome.blade.php'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig);
        $this->assertFileHasContent("'disks' => [", $filesystemsConfig);

        $this->assertFileExists($composerJson);
        $this->assertFileHasContent('<body>', $composerJson);
    }

    /** @test */
    public function it_can_export_folders()
    {
        $this->setExportPaths([
            'config',
            'resources/views',
        ]);

        $this->assertFileNotExists($this->exportPath('config'));
        $this->assertFileNotExists($this->exportPath('resources/views'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileHasContent("'disks' => [", $filesystemsConfig);

        $this->assertFileExists($this->exportPath('config/app.php'));
        $this->assertFileExists($this->exportPath('resources/views/errors'));
        $this->assertFileNotExists($this->exportPath('resources/js'));
    }

    /** @test */
    public function it_copies_export_config()
    {
        $this->setExportPaths([
            'config',
        ]);

        $this->assertFileNotExists($starterKitConfig = $this->exportPath('starter-kit.yaml'));

        $this->exportCoolRunnings();

        $this->assertFileExists($starterKitConfig);
        $this->assertFileHasContent('export_paths:', $starterKitConfig);
    }

    /** @test */
    public function it_does_not_export_opinionated_app_composer_json()
    {
        $this->setExportPaths([
            'composer.json',
        ]);

        $this->assertFileExists(base_path('composer.json'));

        $this->exportCoolRunnings();

        $this->assertFileNotExists($this->exportPath('composer.json'));
    }

    /** @test */
    public function it_exports_basic_composer_json_file()
    {
        $this->setExportPaths([
            'config',
        ]);

        $this->assertFileExists(base_path('composer.json'));

        $this->exportCoolRunnings();

        $this->assertEquals(<<<'EOT'
{
    "name": "my-vendor-name/cool-runnings",
    "extra": {
        "statamic": {
            "name": "Cool Runnings",
            "description": "Cool Runnings starter kit"
        }
    }
}
EOT
        , $this->files->get($this->exportPath('composer.json')));
    }

    /** @test */
    public function it_copies_exportable_dependencies_into_a_clean_composer_json_file()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $this->setExportableDependencies([
            'statamic/ssg',
            'statamic/seo-pro',
        ]);

        $this->assertFileNotExists($this->exportPath('composer.json'));

        $this->exportCoolRunnings();

        $this->assertFileExists($this->exportPath('composer.json'));

        $this->assertEquals(<<<'EOT'
{
    "name": "my-vendor-name/cool-runnings",
    "extra": {
        "statamic": {
            "name": "Cool Runnings",
            "description": "Cool Runnings starter kit"
        }
    },
    "require": {
        "statamic/seo-pro": "^2.2",
        "statamic/ssg": "^0.4.0"
    }
}
EOT
        , $this->files->get($this->exportPath('composer.json')));
    }

    /** @test */
    public function it_copies_exportable_dev_dependencies_into_a_clean_composer_json_file()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*"
    },
    "require-dev": {
        "statamic/seo-pro": "^2.2",
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $this->setExportableDependencies([
            'statamic/ssg',
            'statamic/seo-pro',
        ]);

        $this->assertFileNotExists($this->exportPath('composer.json'));

        $this->exportCoolRunnings();

        $this->assertFileExists($this->exportPath('composer.json'));

        $this->assertEquals(<<<'EOT'
{
    "name": "my-vendor-name/cool-runnings",
    "extra": {
        "statamic": {
            "name": "Cool Runnings",
            "description": "Cool Runnings starter kit"
        }
    },
    "require-dev": {
        "statamic/seo-pro": "^2.2",
        "statamic/ssg": "^0.4.0"
    }
}
EOT
        , $this->files->get($this->exportPath('composer.json')));
    }

    /** @test */
    public function it_copies_all_exportable_dependencies_into_a_clean_composer_json_file()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $this->setExportableDependencies([
            'statamic/ssg',
            'statamic/seo-pro',
        ]);

        $this->assertFileNotExists($this->exportPath('composer.json'));

        $this->exportCoolRunnings();

        $this->assertFileExists($this->exportPath('composer.json'));

        $this->assertEquals(<<<'EOT'
{
    "name": "my-vendor-name/cool-runnings",
    "extra": {
        "statamic": {
            "name": "Cool Runnings",
            "description": "Cool Runnings starter kit"
        }
    },
    "require": {
        "statamic/seo-pro": "^2.2"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    }
}
EOT
        , $this->files->get($this->exportPath('composer.json')));
    }

    private function exportPath($path = null)
    {
        return collect([$this->exportPath, $path])->filter()->implode('/');
    }

    private function setExportPaths($paths)
    {
        $config['export_paths'] = $paths;

        $this->files->put($this->configPath, YAML::dump($config));

        $this->files->makeDirectory($this->exportPath);
    }

    private function setExportableDependencies($dependencies)
    {
        $config['export_paths'] = ['config']; // Dummy export paths to prevent command failure.

        $config['dependencies'] = $dependencies;

        $this->files->put($this->configPath, YAML::dump($config));

        $this->files->makeDirectory($this->exportPath);
    }

    private function exportCoolRunnings()
    {
        $this->artisan('statamic:starter-kit:export ../cool-runnings');
    }

    private function assertFileHasContent($expected, $path)
    {
        $this->assertFileExists($path);

        $this->assertStringContainsString($expected, $this->files->get($path));
    }
}
