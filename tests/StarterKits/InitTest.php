<?php

namespace Tests\StarterKits;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InitTest extends TestCase
{
    use Concerns\BacksUpComposerJson;

    protected $files;
    protected $packagePath;
    protected $targetPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
        $this->packagePath = base_path('package');

        $this->cleanUp();
    }

    public function tearDown(): void
    {
        $this->cleanUp();

        parent::tearDown();
    }

    private function cleanUp()
    {
        if ($this->files->exists($this->packagePath)) {
            $this->files->deleteDirectory($this->packagePath);
        }
    }

    #[Test]
    public function it_can_init_basic_config()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKit()
            ->expectsOutputToContain('You can manage your starter kit\'s package config in [package/composer.json] at any time.')
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(2, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'YAML'
# export_paths:
#   - content
#   - config/filesystems.php
#   - config/statamic/assets.php
#   - resources/blueprints
#   - resources/css/site.css
#   - resources/views
#   - public/build
#   - package.json
#   - tailwind.config.js
#   - vite.config.js
YAML, trim($this->files->get($configPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "example/starter-kit-package",
    "extra": {
        "statamic": {
            "name": "Example Name",
            "description": "A description of your starter kit"
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));
    }

    #[Test]
    public function it_can_interactively_init_basic_config()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKitInteractively()
            ->expectsQuestion('Starter Kit Package (eg. hasselhoff/kung-fury)', null)
            ->expectsQuestion('Starter Kit Name (eg. Kung Fury)', null)
            ->expectsQuestion('Starter Kit Description', null)
            ->expectsConfirmation('Would you like to make this starter-kit updatable?', 'no')
            ->expectsOutputToContain('You can manage your starter kit\'s package config in [package/composer.json] at any time.')
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(2, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'YAML'
# export_paths:
#   - content
#   - config/filesystems.php
#   - config/statamic/assets.php
#   - resources/blueprints
#   - resources/css/site.css
#   - resources/views
#   - public/build
#   - package.json
#   - tailwind.config.js
#   - vite.config.js
YAML, trim($this->files->get($configPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "example/starter-kit-package",
    "extra": {
        "statamic": {
            "name": "Example Name",
            "description": "A description of your starter kit"
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));
    }

    #[Test]
    public function it_can_init_with_custom_package_info()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKit([
                'package' => 'statamic/starter-kit-cool-writings',
                '--name' => 'Cool Writings',
                '--description' => 'A Cool Runnings inspired starter kit.',
            ])
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(2, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "statamic/starter-kit-cool-writings",
    "extra": {
        "statamic": {
            "name": "Cool Writings",
            "description": "A Cool Runnings inspired starter kit."
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));
    }

    #[Test]
    public function it_validates_package()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKit([
                'package' => 'statamic',
            ])
            ->expectsOutputToContain('Must be a valid composer package name (eg. hasselhoff/kung-fury).')
            ->doesntExpectOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertFailed();

        $this->assertFileDoesNotExist($this->packagePath());
    }

    #[Test]
    public function it_can_interactively_init_with_custom_package_info()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKitInteractively()
            ->expectsQuestion('Starter Kit Package (eg. hasselhoff/kung-fury)', 'statamic/starter-kit-cool-writings')
            ->expectsQuestion('Starter Kit Name (eg. Kung Fury)', 'Cool Writings')
            ->expectsQuestion('Starter Kit Description', 'A Cool Runnings inspired starter kit.')
            ->expectsConfirmation('Would you like to make this starter-kit updatable?', 'no')
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(2, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "statamic/starter-kit-cool-writings",
    "extra": {
        "statamic": {
            "name": "Cool Writings",
            "description": "A Cool Runnings inspired starter kit."
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));
    }

    #[Test]
    public function it_can_init_updatable_kit()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKit(['--updatable' => true])
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(3, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));
        $this->assertFileExists($serviceProviderPath = $this->packagePath('src/ServiceProvider.php'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'YAML'
updatable: true
# export_paths:
#   - content
#   - config/filesystems.php
#   - config/statamic/assets.php
#   - resources/blueprints
#   - resources/css/site.css
#   - resources/views
#   - public/build
#   - package.json
#   - tailwind.config.js
#   - vite.config.js
YAML, trim($this->files->get($configPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "example/starter-kit-package",
    "extra": {
        "statamic": {
            "name": "Example Name",
            "description": "A description of your starter kit"
        },
        "laravel": {
            "providers": [
                "Example\\StarterKitNamespace\\ServiceProvider"
            ]
        }
    },
    "autoload": {
        "psr-4": {
            "Example\\StarterKitNamespace\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests"
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'PHP'
<?php

namespace Example\StarterKitNamespace;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        //
    }
}
PHP, trim($this->files->get($serviceProviderPath)));
    }

    #[Test]
    public function it_can_interactively_init_updatable_kit()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKitInteractively()
            ->expectsQuestion('Starter Kit Package (eg. hasselhoff/kung-fury)', null)
            ->expectsQuestion('Starter Kit Name (eg. Kung Fury)', null)
            ->expectsQuestion('Starter Kit Description', null)
            ->expectsConfirmation('Would you like to make this starter-kit updatable?', 'yes')
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(3, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));
        $this->assertFileExists($serviceProviderPath = $this->packagePath('src/ServiceProvider.php'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'YAML'
updatable: true
# export_paths:
#   - content
#   - config/filesystems.php
#   - config/statamic/assets.php
#   - resources/blueprints
#   - resources/css/site.css
#   - resources/views
#   - public/build
#   - package.json
#   - tailwind.config.js
#   - vite.config.js
YAML, trim($this->files->get($configPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "example/starter-kit-package",
    "extra": {
        "statamic": {
            "name": "Example Name",
            "description": "A description of your starter kit"
        },
        "laravel": {
            "providers": [
                "Example\\StarterKitNamespace\\ServiceProvider"
            ]
        }
    },
    "autoload": {
        "psr-4": {
            "Example\\StarterKitNamespace\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests"
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'PHP'
<?php

namespace Example\StarterKitNamespace;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        //
    }
}
PHP, trim($this->files->get($serviceProviderPath)));
    }

    #[Test]
    public function it_can_init_updatable_kit_with_custom_package_info()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKit([
                'package' => 'statamic-rad-pack/starter-kit-cool-writings',
                '--name' => 'Cool Writings',
                '--description' => 'A Cool Runnings inspired starter kit.',
                '--updatable' => true,
            ])
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(3, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));
        $this->assertFileExists($serviceProviderPath = $this->packagePath('src/ServiceProvider.php'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "statamic-rad-pack/starter-kit-cool-writings",
    "extra": {
        "statamic": {
            "name": "Cool Writings",
            "description": "A Cool Runnings inspired starter kit."
        },
        "laravel": {
            "providers": [
                "StatamicRadPack\\CoolWritings\\ServiceProvider"
            ]
        }
    },
    "autoload": {
        "psr-4": {
            "StatamicRadPack\\CoolWritings\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests"
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'PHP'
<?php

namespace StatamicRadPack\CoolWritings;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        //
    }
}
PHP, trim($this->files->get($serviceProviderPath)));
    }

    #[Test]
    public function it_can_interactively_init_updatable_kit_with_custom_package_info()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKitInteractively()
            ->expectsQuestion('Starter Kit Package (eg. hasselhoff/kung-fury)', 'statamic-rad-pack/starter-kit-cool-writings')
            ->expectsQuestion('Starter Kit Name (eg. Kung Fury)', 'Cool Writings')
            ->expectsQuestion('Starter Kit Description', 'A Cool Runnings inspired starter kit.')
            ->expectsConfirmation('Would you like to make this starter-kit updatable?', 'yes')
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(3, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));
        $this->assertFileExists($serviceProviderPath = $this->packagePath('src/ServiceProvider.php'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "statamic-rad-pack/starter-kit-cool-writings",
    "extra": {
        "statamic": {
            "name": "Cool Writings",
            "description": "A Cool Runnings inspired starter kit."
        },
        "laravel": {
            "providers": [
                "StatamicRadPack\\CoolWritings\\ServiceProvider"
            ]
        }
    },
    "autoload": {
        "psr-4": {
            "StatamicRadPack\\CoolWritings\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests"
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'PHP'
<?php

namespace StatamicRadPack\CoolWritings;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        //
    }
}
PHP, trim($this->files->get($serviceProviderPath)));
    }

    #[Test]
    public function it_properly_camel_cases_namespace()
    {
        $this->assertFileDoesNotExist($this->packagePath());

        $this
            ->initStarterKit([
                '--name' => 'Cool-Writings_Kit ABC',
                '--updatable' => true,
            ])
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(3, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));
        $this->assertFileExists($serviceProviderPath = $this->packagePath('src/ServiceProvider.php'));

        $this->assertStringContainsString('"Example\\\\CoolWritingsKitABC\\\\": "src"', $this->files->get($composerJsonPath));
        $this->assertStringContainsString('"Example\\\\CoolWritingsKitABC\\\\ServiceProvider"', $this->files->get($composerJsonPath));
        $this->assertStringContainsString('namespace Example\\CoolWritingsKitABC;', $this->files->get($serviceProviderPath));
    }

    #[Test]
    public function it_asks_to_overwrite_existing_files_interactively()
    {
        $this->files->makeDirectory($this->packagePath());
        $this->files->put($configPath = $this->packagePath('starter-kit.yaml'), $config = 'existing starter-kit.yaml!');
        $this->files->put($composerJsonPath = $this->packagePath('composer.json'), $composerJson = 'existing composer.json!');

        $this
            ->initStarterKitInteractively()
            ->expectsQuestion('Starter Kit Package (eg. hasselhoff/kung-fury)', 'statamic/starter-kit-cool-writings')
            ->expectsQuestion('Starter Kit Name (eg. Kung Fury)', 'Cool Writings')
            ->expectsQuestion('Starter Kit Description', 'A Cool Runnings inspired starter kit.')
            ->expectsConfirmation('Would you like to make this starter-kit updatable?', 'no')
            ->expectsConfirmation('A [starter-kit.yaml] config already exists. Would you like to overwrite it?', 'no')
            ->expectsConfirmation('A [composer.json] config already exists. Would you like to overwrite it?', 'no')
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(2, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));

        $this->assertEquals($config, trim($this->files->get($configPath)));
        $this->assertEquals($composerJson, trim($this->files->get($composerJsonPath)));
    }

    #[Test]
    public function it_can_overwrite_existing_files_interactively()
    {
        $this->files->makeDirectory($this->packagePath());
        $this->files->put($configPath = $this->packagePath('starter-kit.yaml'), $config = 'existing starter-kit.yaml!');
        $this->files->put($composerJsonPath = $this->packagePath('composer.json'), $composerJson = 'existing composer.json!');

        $this
            ->initStarterKitInteractively()
            ->expectsQuestion('Starter Kit Package (eg. hasselhoff/kung-fury)', 'statamic/starter-kit-cool-writings')
            ->expectsQuestion('Starter Kit Name (eg. Kung Fury)', 'Cool Writings')
            ->expectsQuestion('Starter Kit Description', 'A Cool Runnings inspired starter kit.')
            ->expectsConfirmation('Would you like to make this starter-kit updatable?', 'no')
            ->expectsConfirmation('A [starter-kit.yaml] config already exists. Would you like to overwrite it?', 'yes')
            ->expectsConfirmation('A [composer.json] config already exists. Would you like to overwrite it?', 'yes')
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(2, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'YAML'
# export_paths:
#   - content
#   - config/filesystems.php
#   - config/statamic/assets.php
#   - resources/blueprints
#   - resources/css/site.css
#   - resources/views
#   - public/build
#   - package.json
#   - tailwind.config.js
#   - vite.config.js
YAML, trim($this->files->get($configPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "statamic/starter-kit-cool-writings",
    "extra": {
        "statamic": {
            "name": "Cool Writings",
            "description": "A Cool Runnings inspired starter kit."
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));
    }

    #[Test]
    public function it_always_overwrites_existing_files_when_run_non_interactively()
    {
        $this->files->makeDirectory($this->packagePath());
        $this->files->put($configPath = $this->packagePath('starter-kit.yaml'), $config = 'existing starter-kit.yaml!');
        $this->files->put($composerJsonPath = $this->packagePath('composer.json'), $composerJson = 'existing composer.json!');

        $this
            ->initStarterKit(['package' => 'statamic/starter-kit-cool-writings'])
            ->expectsOutputToContain('Your starter kit config was successfully created in your project\'s [package] folder.')
            ->assertOk();

        $this->assertCount(2, $this->files->allFiles($this->packagePath()));

        $this->assertFileExists($configPath = $this->packagePath('starter-kit.yaml'));
        $this->assertFileExists($composerJsonPath = $this->packagePath('composer.json'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'YAML'
# export_paths:
#   - content
#   - config/filesystems.php
#   - config/statamic/assets.php
#   - resources/blueprints
#   - resources/css/site.css
#   - resources/views
#   - public/build
#   - package.json
#   - tailwind.config.js
#   - vite.config.js
YAML, trim($this->files->get($configPath)));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'JSON'
{
    "name": "statamic/starter-kit-cool-writings",
    "extra": {
        "statamic": {
            "name": "Example Name",
            "description": "A description of your starter kit"
        }
    }
}
JSON, trim($this->files->get($composerJsonPath)));
    }

    private function packagePath($path = null)
    {
        return collect([$this->packagePath, $path])->filter()->implode('/');
    }

    private function initStarterKit($options = [])
    {
        return $this->artisan('statamic:starter-kit:init', array_merge([
            '--no-interaction' => true,
        ], $options));
    }

    private function initStarterKitInteractively($options = [])
    {
        return $this->artisan('statamic:starter-kit:init', $options);
    }

    private function assertFileHasContent($expected, $path)
    {
        $this->assertFileExists($path);

        $this->assertStringContainsString($expected, $this->files->get($path));
    }
}
