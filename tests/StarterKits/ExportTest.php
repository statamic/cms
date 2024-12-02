<?php

namespace Tests\StarterKits;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use Concerns\BacksUpComposerJson;

    protected $files;
    protected $packagePath;
    protected $exportPath;
    protected $postInstallHookPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
        $this->packagePath = base_path('package');
        $this->postInstallHookPath = base_path('StarterKitPostInstall.php');
        $this->targetPath = base_path('../cool-runnings');

        $this->cleanUp();
        $this->restoreComposerJson();
        $this->backupComposerJson();
    }

    public function tearDown(): void
    {
        $this->cleanUp();
        $this->restoreComposerJson();

        parent::tearDown();
    }

    private function cleanUp()
    {
        if ($this->files->exists($this->packagePath)) {
            $this->files->deleteDirectory($this->packagePath);
        }

        if ($this->files->exists($this->targetPath)) {
            $this->files->deleteDirectory($this->targetPath);
        }

        if ($this->files->exists($this->postInstallHookPath)) {
            $this->files->delete($this->postInstallHookPath);
        }
    }

    #[Test]
    public function it_requires_valid_starter_kit_config()
    {
        $this->assertFileDoesNotExist($source = base_path('package/starter-kit.yaml'));
        $this->assertFileDoesNotExist($target = $this->targetPath('starter-kit.yaml'));

        $this
            ->exportCoolRunnings()
            ->expectsOutputToContain('Starter kit config [package/starter-kit.yaml] does not exist.')
            ->assertFailed();

        $this->assertFileDoesNotExist($source);
        $this->assertFileDoesNotExist($target);
    }

    #[Test]
    public function it_requires_valid_package_composer_json_config()
    {
        $this->setExportPaths([
            'config/filesystems.php',
            'resources/views/welcome.blade.php',
        ]);

        $this->files->delete(base_path('package/composer.json'));

        $this->assertFileExists(base_path('package/starter-kit.yaml'));

        $this->assertFileDoesNotExist($source = base_path('package/composer.json'));
        $this->assertFileDoesNotExist($target = $this->targetPath('composer.json'));

        $this
            ->exportCoolRunnings()
            ->expectsOutputToContain('Package config [package/composer.json] does not exist.')
            ->assertFailed();

        $this->assertFileDoesNotExist($source);
        $this->assertFileDoesNotExist($target);
    }

    #[Test]
    public function it_can_export_files()
    {
        $this->setExportPaths([
            'config/filesystems.php',
            'resources/views/welcome.blade.php',
        ]);

        $this->assertFileDoesNotExist($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileDoesNotExist($welcomeView = $this->exportPath('resources/views/welcome.blade.php'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig);
        $this->assertFileHasContent("'disks' => [", $filesystemsConfig);

        $this->assertFileExists($welcomeView);
        $this->assertFileHasContent('<body', $welcomeView);
    }

    #[Test]
    public function it_can_export_folders()
    {
        $this->setExportPaths([
            'config',
            'resources/views',
        ]);

        $this->assertFileDoesNotExist($this->targetPath('config'));
        $this->assertFileDoesNotExist($this->targetPath('resources/views'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileHasContent("'disks' => [", $filesystemsConfig);

        $this->assertFileExists($this->exportPath('config/app.php'));
        $this->assertFileExists($this->exportPath('resources/views/errors'));
        $this->assertFileDoesNotExist($this->exportPath('resources/js'));
    }

    #[Test]
    public function it_can_still_export_as_to_different_destination_path_for_backwards_compatibility()
    {
        $paths = $this->cleanPaths([
            base_path('README.md'),
            base_path('test-folder'),
        ]);

        $this->files->put(base_path('README.md'), 'This is readme for the new site!');
        $this->files->makeDirectory(base_path('test-folder'));
        $this->files->put(base_path('test-folder/one.txt'), 'One.');
        $this->files->put(base_path('test-folder/two.txt'), 'Two.');

        $this->setExportPaths([], [
            'README.md' => 'README-new-site.md',
            'test-folder' => 'test-renamed-folder',
        ]);

        $this->assertFileDoesNotExist($renamedFile = $this->exportPath('README-new-site.md'));
        $this->assertFileDoesNotExist($renamedFolder = $this->exportPath('test-renamed-folder'));

        $this->exportCoolRunnings();

        $this->assertFileExists($renamedFile);
        $this->assertFileExists($renamedFolder);

        $this->assertFileDoesNotExist($this->exportPath('README.md')); // This got renamed above
        $this->assertFileDoesNotExist($this->exportPath('test-folder')); // This got renamed above

        $this->assertFileHasContent('This is readme for the new site!', $renamedFile);
        $this->assertFileHasContent('One.', $renamedFolder.'/one.txt');
        $this->assertFileHasContent('Two.', $renamedFolder.'/two.txt');

        $this->cleanPaths($paths);
    }

    #[Test]
    public function it_can_clear_target_export_path_with_clear_option()
    {
        $paths = $this->cleanPaths([
            base_path('one'),
            base_path('two'),
        ]);

        // Imagine this exists from previous export
        $this->files->makeDirectory($this->exportPath('one'), 0777, true, true);
        $this->files->put($this->exportPath('one/file.md'), 'One.');

        $this->files->makeDirectory(base_path('two'), 0777, true, true);
        $this->files->put(base_path('two/file.md'), 'Two.');

        $this->setExportPaths([
            'two/file.md',
        ]);

        $this->assertFileExists($this->exportPath('one'));
        $this->assertFileDoesNotExist($this->exportPath('two'));

        $this->exportCoolRunnings();

        // Our 'one' folder should exist after exporting normally
        $this->assertFileExists($this->exportPath('one'));
        $this->assertFileExists($this->exportPath('two'));

        $this->exportCoolRunnings(['--clear' => true]);

        // But 'one' folder should exist after exporting with `--clear` option
        $this->assertFileDoesNotExist($this->exportPath('one'));
        $this->assertFileExists($this->exportPath('two'));

        $this->exportCoolRunnings();

        $this->cleanPaths($paths);
    }

    #[Test]
    public function it_copies_export_config()
    {
        $this->setExportPaths([
            'config',
        ]);

        $this->assertFileDoesNotExist($starterKitConfig = $this->targetPath('starter-kit.yaml'));

        $this->exportCoolRunnings();

        $this->assertFileExists($starterKitConfig);
        $this->assertFileHasContent('export_paths:', $starterKitConfig);
    }

    #[Test]
    public function it_copies_post_install_script_hook_when_available()
    {
        $this->setExportPaths([
            'config',
        ]);

        $this->assertFileDoesNotExist($postInstallHook = $this->targetPath('StarterKitPostInstall.php'));

        $this->files->put(base_path('StarterKitPostInstall.php'), '<?php');

        $this->exportCoolRunnings();

        $this->assertFileExists($postInstallHook);
        $this->assertFileHasContent('<?php', $postInstallHook);
    }

    #[Test]
    public function it_exports_all_dependencies_from_versionless_array()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
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
            'hansolo/falcon',
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigEquals('dependencies', [
            'statamic/seo-pro' => '^2.2',
            'hansolo/falcon' => '*',
        ]);

        $this->assertExportedConfigEquals('dependencies_dev', [
            'statamic/ssg' => '^0.4.0',
        ]);
    }

    #[Test]
    public function it_exports_only_non_dev_dependencies_from_versionless_array()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}

EOT
        );

        $this->setExportableDependencies([
            'statamic/seo-pro',
            'hansolo/falcon',
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigEquals('dependencies', [
            'statamic/seo-pro' => '^2.2',
            'hansolo/falcon' => '*',
        ]);

        $this->assertExportedConfigDoesNotHave('dependencies_dev');
    }

    #[Test]
    public function it_exports_only_dev_dependencies_from_versionless_array()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}

EOT
        );

        $this->setExportableDependencies([
            'dependencies_dev' => [
                'statamic/ssg',
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigDoesNotHave('dependencies');

        $this->assertExportedConfigEquals('dependencies_dev', [
            'statamic/ssg' => '^0.4.0',
        ]);
    }

    #[Test]
    public function it_overrides_all_dependencies_from_composer_json()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}

EOT
        );

        $this->setExportableDependencies([
            'dependencies' => [
                'statamic/ssg' => '10.0.0',
                'statamic/seo-pro' => '10.0.0',
            ],
            'dependencies_dev' => [
                'hansolo/falcon' => '10.0.0',
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigEquals('dependencies', [
            'statamic/seo-pro' => '^2.2',
            'hansolo/falcon' => '*',
        ]);

        $this->assertExportedConfigEquals('dependencies_dev', [
            'statamic/ssg' => '^0.4.0',
        ]);
    }

    #[Test]
    public function it_overrides_non_dev_dependencies_from_composer_json()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}

EOT
        );

        $this->setExportableDependencies([
            'dependencies' => [
                'statamic/seo-pro' => '10.0.0',
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigEquals('dependencies', [
            'statamic/seo-pro' => '^2.2',
        ]);

        $this->assertExportedConfigDoesNotHave('dependencies_dev');
    }

    #[Test]
    public function it_overrides_dev_dependencies_from_composer_json()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $this->setExportableDependencies([
            'dependencies_dev' => [
                'statamic/ssg' => '10.0.0',
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigDoesNotHave('dependencies');

        $this->assertExportedConfigEquals('dependencies_dev', [
            'statamic/ssg' => '^0.4.0',
        ]);
    }

    #[Test]
    public function it_correctly_categorizes_non_dev_dependencies_from_composer_json()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}

EOT
        );

        // this is actually a dev dependency, so it should get converted to dev dependency
        $this->setExportableDependencies([
            'dependencies' => [
                'statamic/ssg',
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigDoesNotHave('dependencies');

        $this->assertExportedConfigEquals('dependencies_dev', [
            'statamic/ssg' => '^0.4.0',
        ]);
    }

    #[Test]
    public function it_correctly_categorizes_dev_dependencies_from_composer_json()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}

EOT
        );

        // this is actually a non-dev dependency, so it should get converted to non-dev dependency
        $this->setExportableDependencies([
            'dependencies_dev' => [
                'hansolo/falcon',
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigEquals('dependencies', [
            'hansolo/falcon' => '*',
        ]);

        $this->assertExportedConfigDoesNotHave('dependencies_dev');
    }

    #[Test]
    public function it_does_not_export_opinionated_app_composer_json()
    {
        $this->setExportPaths([
            'composer.json',
        ]);

        $this->assertFileExists(base_path('composer.json'));

        $this->exportCoolRunnings();

        $this->assertFileDoesNotExist($this->targetPath('composer.json'));
    }

    #[Test]
    public function it_exports_custom_package_composer_json_file()
    {
        $this->setExportPaths([
            'config',
        ]);

        $this->files->put(base_path('package/composer.json'), $composerJson = 'custom composer.json!');

        $this->assertFileExists(base_path('composer.json'));
        $this->assertFileDoesNotExist($filesystemsConfig = $this->exportPath('config/filesystems.php'));

        $this->exportCoolRunnings();

        $this->assertEquals($composerJson, $this->files->get($this->targetPath('composer.json')));
        $this->assertFileExists($filesystemsConfig);
    }

    #[Test]
    public function it_can_export_module_files()
    {
        $this->setConfig([
            'modules' => [
                'seo' => [
                    'export_paths' => [
                        'config/filesystems.php',
                    ],
                ],
                'ssg' => [
                    'export_as' => [
                        'resources/views/welcome.blade.php' => 'resources/views/you-are-so-welcome.blade.php',
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileDoesNotExist($welcomeView = $this->exportPath('resources/views/you-are-so-welcome.blade.php'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig);
        $this->assertFileHasContent("'disks' => [", $filesystemsConfig);

        $this->assertFileExists($welcomeView);
        $this->assertFileHasContent('<body', $welcomeView);
    }

    #[Test]
    public function it_can_export_nested_module_files()
    {
        $this->setConfig([
            'modules' => [
                'seo' => [
                    'export_paths' => [
                        'config/filesystems.php',
                    ],
                    'modules' => [
                        'ssg' => [
                            'export_as' => [
                                'resources/views/welcome.blade.php' => 'resources/views/you-are-so-welcome.blade.php',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileDoesNotExist($welcomeView = $this->exportPath('resources/views/you-are-so-welcome.blade.php'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig);
        $this->assertFileHasContent("'disks' => [", $filesystemsConfig);

        $this->assertFileExists($welcomeView);
        $this->assertFileHasContent('<body', $welcomeView);
    }

    #[Test]
    public function it_can_export_select_module_files()
    {
        $this->setConfig([
            'modules' => [
                'js' => [
                    'options' => [
                        'vue' => [
                            'export_paths' => [
                                'config/filesystems.php',
                            ],
                        ],
                        'react' => [
                            'export_as' => [
                                'resources/views/welcome.blade.php' => 'resources/views/you-are-so-welcome.blade.php',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileDoesNotExist($welcomeView = $this->exportPath('resources/views/you-are-so-welcome.blade.php'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig);
        $this->assertFileHasContent("'disks' => [", $filesystemsConfig);

        $this->assertFileExists($welcomeView);
        $this->assertFileHasContent('<body', $welcomeView);
    }

    #[Test]
    public function it_can_export_nested_select_module_files()
    {
        $this->setConfig([
            'modules' => [
                'js' => [
                    'options' => [
                        'vue' => [
                            'export_paths' => [
                                'config/filesystems.php',
                            ],
                            'modules' => [
                                'testing_tools' => [
                                    'export_paths' => [
                                        'config/app.php',
                                    ],
                                ],
                            ],
                        ],
                        'react' => [
                            'export_as' => [
                                'resources/views/welcome.blade.php' => 'resources/views/you-are-so-welcome.blade.php',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileDoesNotExist($appConfig = $this->exportPath('config/app.php'));
        $this->assertFileDoesNotExist($welcomeView = $this->exportPath('resources/views/you-are-so-welcome.blade.php'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig);
        $this->assertFileHasContent("'disks' => [", $filesystemsConfig);

        $this->assertFileExists($appConfig);
        $this->assertFileHasContent("'url' => env(", $appConfig);

        $this->assertFileExists($welcomeView);
        $this->assertFileHasContent('<body', $welcomeView);
    }

    #[Test]
    public function it_can_export_module_dependencies()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $this->setConfig([
            'modules' => [
                'seo' => [
                    'dependencies' => [
                        'statamic/seo-pro',
                    ],
                ],
                'ssg' => [
                    'dependencies_dev' => [
                        'statamic/ssg',
                    ],
                ],
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigEquals('modules.seo.dependencies', [
            'statamic/seo-pro' => '^2.2',
        ]);

        $this->assertExportedConfigDoesNotHave('modules.seo.dependencies_dev');

        $this->assertExportedConfigEquals('modules.ssg.dependencies_dev', [
            'statamic/ssg' => '^0.4.0',
        ]);

        $this->assertExportedConfigDoesNotHave('modules.ssg.dependencies');
    }

    #[Test]
    public function it_can_export_nested_module_dependencies()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $this->setConfig([
            'modules' => [
                'seo' => [
                    'dependencies' => [
                        'statamic/seo-pro',
                    ],
                    'modules' => [
                        'ssg' => [
                            'dependencies_dev' => [
                                'statamic/ssg',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigEquals('modules.seo.dependencies', [
            'statamic/seo-pro' => '^2.2',
        ]);

        $this->assertExportedConfigDoesNotHave('modules.seo.dependencies_dev');

        $this->assertExportedConfigEquals('modules.seo.modules.ssg.dependencies_dev', [
            'statamic/ssg' => '^0.4.0',
        ]);

        $this->assertExportedConfigDoesNotHave('modules.ssg.dependencies');
    }

    #[Test]
    public function it_can_export_select_module_dependencies()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $this->setConfig([
            'modules' => [
                'first_party' => [
                    'options' => [
                        'seo' => [
                            'dependencies' => [
                                'statamic/seo-pro',
                            ],
                        ],
                        'ssg' => [
                            'dependencies_dev' => [
                                'statamic/ssg',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigEquals('modules.first_party.options.seo.dependencies', [
            'statamic/seo-pro' => '^2.2',
        ]);

        $this->assertExportedConfigDoesNotHave('modules.first_party.seo.dependencies_dev');

        $this->assertExportedConfigEquals('modules.first_party.options.ssg.dependencies_dev', [
            'statamic/ssg' => '^0.4.0',
        ]);

        $this->assertExportedConfigDoesNotHave('modules.first_party.ssg.dependencies');
    }

    #[Test]
    public function it_can_export_nested_select_module_dependencies()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $this->setConfig([
            'modules' => [
                'first_party' => [
                    'options' => [
                        'seo' => [
                            'dependencies' => [
                                'statamic/seo-pro',
                            ],
                            'modules' => [
                                'ssg' => [
                                    'dependencies_dev' => [
                                        'statamic/ssg',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertExportedConfigEquals('modules.first_party.options.seo.dependencies', [
            'statamic/seo-pro' => '^2.2',
        ]);

        $this->assertExportedConfigDoesNotHave('modules.first_party.seo.dependencies_dev');

        $this->assertExportedConfigEquals('modules.first_party.options.seo.modules.ssg.dependencies_dev', [
            'statamic/ssg' => '^0.4.0',
        ]);

        $this->assertExportedConfigDoesNotHave('modules.first_party.ssg.dependencies');
    }

    #[Test]
    public function it_requires_valid_config_at_top_level()
    {
        $this->setConfig([
            // no installable config!
        ]);

        $this->assertFileDoesNotExist($welcomeView = $this->targetPath('resources/views/welcome.blade.php'));

        $this
            ->exportCoolRunnings()
            ->expectsOutputToContain('Starter-kit module is missing `export_paths`, `dependencies`, or nested `modules`.')
            ->assertFailed();

        $this->assertFileDoesNotExist($welcomeView);
    }

    #[Test]
    public function it_requires_valid_module_config()
    {
        $this->setConfig([
            'modules' => [
                'seo' => [
                    // no exportable config!
                ],
            ],
        ]);

        $this->assertFileDoesNotExist($welcomeView = $this->targetPath('resources/views/welcome.blade.php'));

        $this
            ->exportCoolRunnings()
            ->expectsOutputToContain('Starter-kit module is missing `export_paths`, `dependencies`, or nested `modules`.')
            ->assertFailed();

        $this->assertFileDoesNotExist($welcomeView);
    }

    #[Test]
    public function it_doesnt_require_anything_installable_if_module_contains_nested_modules()
    {
        $this->setConfig([
            'modules' => [
                'seo' => [
                    'modules' => [
                        'js' => [
                            'export_paths' => [
                                'resources/views/welcome.blade.php',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist($welcomeView = $this->exportPath('resources/views/welcome.blade.php'));

        $this
            ->exportCoolRunnings()
            ->assertSuccessful();

        $this->assertFileExists($welcomeView);
    }

    #[Test]
    #[DataProvider('validModuleConfigs')]
    public function it_passes_validation_if_module_export_paths_or_dependencies_or_nested_modules_are_properly_configured($config)
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $this->setConfig([
            'modules' => [
                'seo' => array_merge(['prompt' => false], $config),
            ],
        ]);

        $this
            ->exportCoolRunnings()
            ->assertSuccessful();
    }

    public static function validModuleConfigs()
    {
        return [
            'export paths' => [[
                'export_paths' => [
                    'resources/views/welcome.blade.php',
                ],
            ]],
            'export as paths' => [[
                'export_as' => [
                    'resources/views/welcome.blade.php' => 'resources/js/vue.js',
                ],
            ]],
            'dependencies' => [[
                'dependencies' => [
                    'statamic/seo-pro' => '^1.0',
                ],
            ]],
            'dev dependencies' => [[
                'dependencies_dev' => [
                    'statamic/seo-pro' => '^1.0',
                ],
            ]],
            'nested modules' => [[
                'modules' => [
                    'filesystem' => [
                        'export_paths' => [
                            'config/filesystems.php',
                        ],
                    ],
                ],
            ]],
        ];
    }

    #[Test]
    #[DataProvider('nonExistentExportPaths')]
    public function it_fails_validation_if_module_export_paths_do_not_exist($config)
    {
        $this->setConfig($config);

        $this
            ->exportCoolRunnings()
            ->expectsOutputToContain('Cannot export [non-existent.txt], because it does not exist in your app.')
            ->assertFailed();
    }

    public static function nonExistentExportPaths()
    {
        return [
            'top level export' => [[
                'export_paths' => [
                    'non-existent.txt',
                ],
            ]],
            'top level export as from' => [[
                'export_as' => [
                    'non-existent.txt' => 'resources/views/welcome.blade.php',
                ],
            ]],
            'module export' => [[
                'modules' => [
                    'seo' => [
                        'export_paths' => [
                            'non-existent.txt',
                        ],
                    ],
                ],
            ]],
            'module export as from' => [[
                'modules' => [
                    'seo' => [
                        'export_as' => [
                            'non-existent.txt' => 'resources/views/welcome.blade.php',
                        ],
                    ],
                ],
            ]],
            'select module export' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'export_paths' => [
                                    'non-existent.txt',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            'select module export as from' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'export_as' => [
                                    'non-existent.txt' => 'resources/views/welcome.blade.php',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
        ];
    }

    #[Test]
    #[DataProvider('nonExistentDependencies')]
    public function it_fails_validation_if_module_dependencies_are_not_installed_in_composer_json($config)
    {
        $this->setConfig($config);

        $this
            ->exportCoolRunnings()
            ->expectsOutputToContain('Cannot export [non/existent], because it does not exist in your composer.json.')
            ->assertFailed();
    }

    public static function nonExistentDependencies()
    {
        return [
            'top level dependencies' => [[
                'dependencies' => [
                    'non/existent',
                ],
            ]],
            'top level dev dependencies' => [[
                'dependencies_dev' => [
                    'non/existent',
                ],
            ]],
            'module dependencies' => [[
                'modules' => [
                    'seo' => [
                        'dependencies' => [
                            'non/existent',
                        ],
                    ],
                ],
            ]],
            'module dev dependencies' => [[
                'modules' => [
                    'seo' => [
                        'dependencies_dev' => [
                            'non/existent',
                        ],
                    ],
                ],
            ]],
            'select module dependencies' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'dependencies' => [
                                    'non/existent',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            'select module dev dependencies' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'dependencies_dev' => [
                                    'non/existent',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
        ];
    }

    #[Test]
    #[DataProvider('configsExportingStarterKitYaml')]
    public function it_doesnt_allow_starter_kit_config_in_export_paths($config)
    {
        $this->setConfig($config);

        $this
            ->exportCoolRunnings()
            ->expectsOutputToContain('Cannot export [starter-kit.yaml] config.')
            ->assertFailed();
    }

    public static function configsExportingStarterKitYaml()
    {
        return [
            'top level export' => [[
                'export_paths' => [
                    'starter-kit.yaml',
                ],
            ]],
            'top level export as from' => [[
                'export_as' => [
                    'starter-kit.yaml' => 'resources/views/welcome.blade.php',
                ],
            ]],
            'top level export as to' => [[
                'export_as' => [
                    'resources/views/welcome.blade.php' => 'starter-kit.yaml',
                ],
            ]],
            'module export' => [[
                'modules' => [
                    'seo' => [
                        'export_paths' => [
                            'starter-kit.yaml',
                        ],
                    ],
                ],
            ]],
            'module export as from' => [[
                'modules' => [
                    'seo' => [
                        'export_as' => [
                            'starter-kit.yaml' => 'resources/views/welcome.blade.php',
                        ],
                    ],
                ],
            ]],
            'module export as to' => [[
                'modules' => [
                    'seo' => [
                        'export_as' => [
                            'resources/views/welcome.blade.php' => 'starter-kit.yaml',
                        ],
                    ],
                ],
            ]],
            'select module export' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'export_paths' => [
                                    'starter-kit.yaml',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            'select module export as from' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'export_as' => [
                                    'starter-kit.yaml' => 'resources/views/welcome.blade.php',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            'select module export as to' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'export_as' => [
                                    'resources/views/welcome.blade.php' => 'starter-kit.yaml',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
        ];
    }

    #[Test]
    #[DataProvider('configsExportingComposerJson')]
    public function it_doesnt_allow_composer_json_in_export_paths($config)
    {
        $this->setConfig($config);

        $this
            ->exportCoolRunnings()
            ->expectsOutputToContain('Cannot export [composer.json]. Please use `dependencies` array.')
            ->assertFailed();
    }

    public static function configsExportingComposerJson()
    {
        return [
            'top level export' => [[
                'export_paths' => [
                    'composer.json',
                ],
            ]],
            'top level export as from' => [[
                'export_as' => [
                    'composer.json' => 'resources/views/welcome.blade.php',
                ],
            ]],
            'top level export as to' => [[
                'export_as' => [
                    'resources/views/welcome.blade.php' => 'composer.json',
                ],
            ]],
            'module export' => [[
                'modules' => [
                    'seo' => [
                        'export_paths' => [
                            'composer.json',
                        ],
                    ],
                ],
            ]],
            'module export as from' => [[
                'modules' => [
                    'seo' => [
                        'export_as' => [
                            'composer.json' => 'resources/views/welcome.blade.php',
                        ],
                    ],
                ],
            ]],
            'module export as to' => [[
                'modules' => [
                    'seo' => [
                        'export_as' => [
                            'resources/views/welcome.blade.php' => 'composer.json',
                        ],
                    ],
                ],
            ]],
            'select module export' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'export_paths' => [
                                    'composer.json',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            'select module export as from' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'export_as' => [
                                    'composer.json' => 'resources/views/welcome.blade.php',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            'select module export as to' => [[
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => [
                                'export_as' => [
                                    'resources/views/welcome.blade.php' => 'composer.json',
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
        ];
    }

    #[Test]
    public function it_normalizes_module_key_order()
    {
        $this->files->put(base_path('composer.json'), <<<'EOT'
{
    "type": "project",
    "require": {
        "php": "^7.3 || ^8.0",
        "laravel/framework": "^8.0",
        "statamic/cms": "3.1.*",
        "statamic/seo-pro": "^2.2",
        "hansolo/falcon": "*",
        "luke/x-wing": "*"
    },
    "require-dev": {
        "statamic/ssg": "^0.4.0"
    },
    "prefer-stable": true
}
EOT
        );

        $paths = $this->cleanPaths([
            base_path('README.md'),
            base_path('test-folder'),
            resource_path('vue.js'),
            resource_path('vue-testing-tools.js'),
        ]);

        $this->files->put(base_path('README.md'), 'This is a readme!');
        $this->files->makeDirectory(base_path('test-folder'));
        $this->files->put(base_path('test-folder/one.txt'), 'One.');
        $this->files->put(resource_path('vue.js'), 'Vue!');
        $this->files->put(resource_path('vue-testing-tools.js'), 'Vue testing tools!');

        $this->setConfig([
            'modules' => [
                'seo' => [
                    'dependencies_dev' => [
                        'statamic/ssg',
                    ],
                    'prompt' => false,
                    'export_as' => [
                        'README.md' => 'README-new-site.md',
                    ],
                    'dependencies' => [
                        'statamic/seo-pro',
                    ],
                    'export_paths' => [
                        'resources/views',
                    ],
                ],
                'js' => [
                    'prompt' => 'Pick the best JS framework!',
                    'skip_option' => 'Nah',
                    'options' => [
                        'vue' => [
                            'label' => 'Vue JS',
                            'dependencies' => [
                                'hansolo/falcon',
                            ],
                            'modules' => [
                                'testing_tools' => [
                                    'dependencies' => [
                                        'luke/x-wing' => '*',
                                    ],
                                    'export_paths' => [
                                        'resources/vue-testing-tools.js',
                                    ],
                                ],
                            ],
                            'export_paths' => [
                                'resources/vue.js',
                            ],
                        ],
                    ],
                ],
            ],
            'export_as' => [
                'test-folder' => 'test-renamed-folder',
            ],
            'dependencies_dev' => [
                'statamic/ssg',
            ],
            'export_paths' => [
                'config/filesystems.php',
            ],
        ]);

        $this->exportCoolRunnings();

        $this->assertConfigSameOrder([
            'export_paths' => [
                'config/filesystems.php',
            ],
            'export_as' => [
                'test-folder' => 'test-renamed-folder',
            ],
            'dependencies_dev' => [
                'statamic/ssg' => '^0.4.0',
            ],
            'modules' => [
                'seo' => [
                    'prompt' => false,
                    'export_paths' => [
                        'resources/views',
                    ],
                    'export_as' => [
                        'README.md' => 'README-new-site.md',
                    ],
                    'dependencies' => [
                        'statamic/seo-pro' => '^2.2',
                    ],
                    'dependencies_dev' => [
                        'statamic/ssg' => '^0.4.0',
                    ],
                ],
                'js' => [
                    'prompt' => 'Pick the best JS framework!',
                    'skip_option' => 'Nah',
                    'options' => [
                        'vue' => [
                            'label' => 'Vue JS',
                            'export_paths' => [
                                'resources/vue.js',
                            ],
                            'dependencies' => [
                                'hansolo/falcon' => '*',
                            ],
                            'modules' => [
                                'testing_tools' => [
                                    'export_paths' => [
                                        'resources/vue-testing-tools.js',
                                    ],
                                    'dependencies' => [
                                        'luke/x-wing' => '*',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->cleanPaths($paths);
    }

    #[Test]
    public function it_can_help_migrate_to_new_package_folder_convention()
    {
        $this->setExportPaths([
            'config',
        ]);

        $this->files->move(base_path('package/starter-kit.yaml'), base_path('starter-kit.yaml'));
        $this->files->put($this->targetPath('starter-kit.yaml'), 'this should get stomped!');
        $this->files->put($this->targetPath('composer.json'), $packageComposerJson = 'custom composer.json!');
        $this->files->deleteDirectory(base_path('package'));

        $this->assertFileDoesNotExist(base_path('package'));
        $this->assertFileDoesNotExist($filesystemsConfig = $this->exportPath('config/filesystems.php'));

        $this->exportCoolRunnings()
            ->expectsOutputToContain('Starter kit config moved to [package/starter-kit.yaml].')
            ->expectsOutputToContain('Composer package config moved to [package/composer.json].')
            ->assertSuccessful();

        $this->assertFileDoesNotExist(base_path('starter-kit.yaml'));
        $this->assertFileExists(base_path('package/starter-kit.yaml'));

        $expectedConfig = [
            'export_paths' => [
                'config',
            ],
        ];

        $this->assertEquals($expectedConfig, YAML::parse($this->files->get(base_path('package/starter-kit.yaml'))));
        $this->assertEquals($expectedConfig, YAML::parse($this->files->get($this->targetPath('starter-kit.yaml'))));

        $this->assertEquals($packageComposerJson, $this->files->get($this->targetPath('composer.json')));

        $this->assertFileExists($filesystemsConfig);
    }

    private function targetPath($path = null)
    {
        return collect([$this->targetPath, $path])->filter()->implode('/');
    }

    private function exportPath($path = null)
    {
        return collect([$this->targetPath, 'export', $path])->filter()->implode('/');
    }

    private function setConfig($config)
    {
        if (! $this->files->exists($this->packagePath)) {
            $this->files->makeDirectory($this->packagePath);
        }

        $this->files->put($this->packagePath.'/starter-kit.yaml', YAML::dump($config));

        $this->files->put($this->packagePath.'/composer.json', <<<'PACKAGE'
{
    "name": "my-vendor-name/cool-runnings",
    "extra": {
        "statamic": {
            "name": "Cool Runnings",
            "description": "Cool Runnings starter kit"
        }
    }
}
PACKAGE);

        if (! $this->files->exists($this->targetPath)) {
            $this->files->makeDirectory($this->targetPath);
        }
    }

    private function setExportPaths($paths, $exportAs = null)
    {
        $config['export_paths'] = $paths;

        if ($exportAs) {
            $config['export_as'] = $exportAs;
        }

        $this->setConfig($config);
    }

    private function setExportableDependencies($dependencies)
    {
        $config['export_paths'] = ['config']; // Dummy export paths to prevent command failure.

        if (isset($dependencies['dependencies']) || isset($dependencies['dependencies_dev'])) {
            $config = array_merge($config, $dependencies);
        } else {
            $config['dependencies'] = $dependencies;
        }

        $this->setConfig($config);
    }

    private function assertExportedConfigEquals($key, $expectedConfig)
    {
        return $this->assertEquals(
            $expectedConfig,
            Arr::get(YAML::parse($this->files->get($this->targetPath('starter-kit.yaml'))), $key)
        );
    }

    private function assertExportedConfigDoesNotHave($key)
    {
        return $this->assertFalse(
            Arr::has(YAML::parse($this->files->get($this->targetPath('starter-kit.yaml'))), $key)
        );
    }

    private function assertConfigSameOrder($expectedConfig)
    {
        return $this->assertSame(
            $expectedConfig,
            YAML::parse($this->files->get($this->targetPath('starter-kit.yaml')))
        );
    }

    private function exportCoolRunnings($options = [])
    {
        return $this->artisan('statamic:starter-kit:export', array_merge([
            'path' => '../cool-runnings',
            '--no-interaction' => true,
        ], $options));
    }

    private function assertFileHasContent($expected, $path)
    {
        $this->assertFileExists($path);

        $this->assertStringContainsString($expected, $this->files->get($path));
    }

    private function cleanPaths($paths)
    {
        collect($paths)
            ->filter(function ($path) {
                return $this->files->exists($path);
            })
            ->each(function ($path) {
                $this->files->isDirectory($path)
                    ? $this->files->deleteDirectory($path)
                    : $this->files->delete($path);
            });

        return $paths;
    }
}
