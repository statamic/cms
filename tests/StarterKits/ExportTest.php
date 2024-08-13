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
    protected $configPath;
    protected $exportPath;
    protected $postInstallHookPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
        $this->configPath = base_path('starter-kit.yaml');
        $this->postInstallHookPath = base_path('StarterKitPostInstall.php');
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
        if ($this->files->exists($this->configPath)) {
            $this->files->delete($this->configPath);
        }

        if ($this->files->exists($this->postInstallHookPath)) {
            $this->files->delete($this->postInstallHookPath);
        }

        $this->restoreComposerJson();

        parent::tearDown();
    }

    #[Test]
    public function it_can_stub_out_a_new_config()
    {
        $this->assertFileDoesNotExist($this->configPath);

        $this->exportCoolRunnings();

        $this->assertFileExists($this->configPath);
        $this->assertFileHasContent('# export_paths:', $this->configPath);
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

        $this->assertFileDoesNotExist($this->exportPath('config'));
        $this->assertFileDoesNotExist($this->exportPath('resources/views'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileHasContent("'disks' => [", $filesystemsConfig);

        $this->assertFileExists($this->exportPath('config/app.php'));
        $this->assertFileExists($this->exportPath('resources/views/errors'));
        $this->assertFileDoesNotExist($this->exportPath('resources/js'));
    }

    #[Test]
    public function it_can_export_as_to_different_destination_path()
    {
        $paths = $this->cleanPaths([
            base_path('README.md'),
            base_path('test-folder'),
        ]);

        $this->files->put(base_path('README.md'), 'This is readme for the new site!');
        $this->files->makeDirectory(base_path('test-folder'));
        $this->files->put(base_path('test-folder/one.txt'), 'One.');
        $this->files->put(base_path('test-folder/two.txt'), 'Two.');

        $this->setExportPaths([
            'config/filesystems.php',
            'resources/views',
        ], [
            'README.md' => 'README-new-site.md',
            'test-folder' => 'test-renamed-folder',
        ]);

        $this->assertFileDoesNotExist($filesystemsConfig = $this->exportPath('config/filesystems.php'));
        $this->assertFileDoesNotExist($errorsFolder = $this->exportPath('resources/views/errors'));
        $this->assertFileDoesNotExist($renamedFile = $this->exportPath('README-new-site.md'));
        $this->assertFileDoesNotExist($renamedFolder = $this->exportPath('test-renamed-folder'));

        $this->exportCoolRunnings();

        $this->assertFileExists($filesystemsConfig);
        $this->assertFileExists($errorsFolder);
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
    public function it_copies_export_config()
    {
        $this->setExportPaths([
            'config',
        ]);

        $this->assertFileDoesNotExist($starterKitConfig = $this->exportPath('starter-kit.yaml'));

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

        $this->assertFileDoesNotExist($postInstallHook = $this->exportPath('StarterKitPostInstall.php'));

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

        $this->assertFileDoesNotExist($this->exportPath('composer.json'));
    }

    #[Test]
    public function it_does_not_export_as_with_opinionated_app_composer_json()
    {
        $this->setExportPaths([
            'config/filesystems.php',
        ], [
            'composer.json' => 'composer-renamed.json',
        ]);

        $this->assertFileExists(base_path('composer.json'));

        $this->exportCoolRunnings();

        $this->assertFileDoesNotExist($this->exportPath('composer.json'));
    }

    #[Test]
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

    #[Test]
    public function it_uses_existing_composer_json_file()
    {
        $this->files->makeDirectory($this->exportPath);
        $this->files->put($this->exportPath('composer.json'), <<<'EOT'
{
    "name": "custom/hot-runnings",
    "keywords": [
        "jamaica",
        "bob-sled"
    ]
}
EOT
        );

        $this->setExportPaths([
            'config',
        ]);

        $this->exportCoolRunnings();

        $this->assertEquals(<<<'EOT'
{
    "name": "custom/hot-runnings",
    "keywords": [
        "jamaica",
        "bob-sled"
    ]
}

EOT
            , $this->files->get($this->exportPath('composer.json')));
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

        $this->assertFileDoesNotExist($welcomeView = $this->exportPath('resources/views/welcome.blade.php'));

        $this
            ->exportCoolRunnings()
            // ->expectsOutput('Starter-kit module is missing `export_paths` or `dependencies`!') // TODO: Why does this work in InstallTest?
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

        $this->assertFileDoesNotExist($welcomeView = $this->exportPath('resources/views/welcome.blade.php'));

        $this
            ->exportCoolRunnings()
            // ->expectsOutput('Starter-kit module is missing `export_paths` or `dependencies`!') // TODO: Why does this work in InstallTest?
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
            // ->expectsOutput('Cannot export [non-existent.txt], because it does not exist in your app!') // TODO: Why does this work in InstallTest?
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
            // ->expectsOutput('Cannot export [non-existent.txt], because it does not exist in your app!') // TODO: Why does this work in InstallTest?
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
    #[DataProvider('configsExportingComposerJson')]
    public function it_doesnt_allow_exporting_of_composer_json_file($config)
    {
        $this->setConfig($config);

        $this
            ->exportCoolRunnings()
            // ->expectsOutput('Cannot export [composer.json]. Please use `dependencies` array!') // TODO: Why does this work in InstallTest?
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

    private function exportPath($path = null)
    {
        return collect([$this->exportPath, $path])->filter()->implode('/');
    }

    private function setConfig($config)
    {
        $this->files->put($this->configPath, YAML::dump($config));

        if (! $this->files->exists($this->exportPath)) {
            $this->files->makeDirectory($this->exportPath);
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
            Arr::get(YAML::parse($this->files->get($this->exportPath('starter-kit.yaml'))), $key)
        );
    }

    private function assertExportedConfigDoesNotHave($key)
    {
        return $this->assertFalse(
            Arr::has(YAML::parse($this->files->get($this->exportPath('starter-kit.yaml'))), $key)
        );
    }

    private function assertConfigSameOrder($expectedConfig)
    {
        return $this->assertSame(
            $expectedConfig,
            YAML::parse($this->files->get($this->exportPath('starter-kit.yaml')))
        );
    }

    private function exportCoolRunnings()
    {
        return $this->artisan('statamic:starter-kit:export', [
            'path' => '../cool-runnings',
            '--no-interaction' => true,
        ]);
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
