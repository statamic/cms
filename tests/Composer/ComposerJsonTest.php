<?php

namespace Tests\Composer;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Composer\Json;
use Statamic\Console\Composer\Scripts;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ComposerJsonTest extends TestCase
{
    private $files;
    private $path;
    private $backupPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->path = base_path('composer.json');
        $this->backupPath = base_path('composer.json.bak');

        if ($this->files->exists($this->backupPath)) {
            $this->restore();
        } else {
            $this->backup();
        }
    }

    public function tearDown(): void
    {
        $this->restore();

        unset($_ENV['COMPOSER']);

        $this->files->delete(base_path('composer.testing.json'));

        parent::tearDown();
    }

    #[Test]
    public function it_can_detect_if_statamic_pre_update_cmd_is_not_registered()
    {
        $this->assertTrue(Json::isMissingPreUpdateCmd());

        $this->files->put($this->path, json_encode([
            'scripts' => [
                'pre-update-cmd' => [
                    Scripts::class.'::preUpdateCmd',
                    'SomeOtherPackage::preUpdateCmd',
                ],
            ],
        ]));

        $this->assertFalse(Json::isMissingPreUpdateCmd());
    }

    #[Test]
    public function it_reads_the_composer_json_named_by_the_composer_env_var()
    {
        $_ENV['COMPOSER'] = 'composer.testing.json';

        $this->assertEquals(base_path('composer.testing.json'), Json::path());
        $this->assertTrue(Json::isMissingPreUpdateCmd());

        $this->files->put(base_path('composer.testing.json'), json_encode([
            'scripts' => [
                'pre-update-cmd' => [
                    Scripts::class.'::preUpdateCmd',
                ],
            ],
        ]));

        $this->assertFalse(Json::isMissingPreUpdateCmd());
    }

    #[Test]
    #[DataProvider('composerScriptContextProvider')]
    public function it_resolves_the_filenames_in_a_composer_script_context($composerEnv, $expected)
    {
        $this->assertEquals($expected, $this->runInComposerScriptContext($composerEnv));
    }

    public static function composerScriptContextProvider()
    {
        return [
            'without the env var' => [false, "composer.json\ncomposer.lock\n"],
            'with the env var' => ['composer.testing.json', "composer.testing.json\ncomposer.testing.lock\n"],
        ];
    }

    /**
     * When Composer runs a script event, it registers the class autoloader but never runs the
     * `autoload.files` entries, so none of Laravel's helper functions exist. Replicate that
     * in a subprocess to ensure we don't reach for anything that depends on them.
     */
    private function runInComposerScriptContext($composerEnv)
    {
        $script = <<<'EOT'
require 'vendor/composer/ClassLoader.php';

$loader = new Composer\Autoload\ClassLoader;

foreach (require 'vendor/composer/autoload_psr4.php' as $namespace => $paths) {
    $loader->setPsr4($namespace, $paths);
}

$loader->addClassMap(require 'vendor/composer/autoload_classmap.php');
$loader->register();

echo Statamic\Console\Composer\Json::filename()."\n";
echo Statamic\Console\Composer\Lock::filename()."\n";
EOT;

        $process = new Process(
            ['php', '-r', $script],
            realpath(__DIR__.'/../..'),
            ['COMPOSER' => $composerEnv],
        );

        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());

        return $process->getOutput();
    }

    #[Test]
    public function it_adds_pre_update_cmd_to_the_composer_json_named_by_the_composer_env_var()
    {
        $_ENV['COMPOSER'] = 'composer.testing.json';

        $original = $this->files->get($this->path);

        $this->files->put(base_path('composer.testing.json'), <<<'EOT'
{
    "scripts": {
        "post-autoload-dump": [
            "@php artisan package:discover --ansi"
        ]
    }
}
EOT
        );

        Json::addPreUpdateCmd();

        $this->assertFalse(Json::isMissingPreUpdateCmd());
        $this->assertEquals($original, $this->files->get($this->path));

        $expected = <<<'EOT'
{
    "scripts": {
        "pre-update-cmd": [
            "Statamic\\Console\\Composer\\Scripts::preUpdateCmd"
        ],
        "post-autoload-dump": [
            "@php artisan package:discover --ansi"
        ]
    }
}
EOT;

        $this->assertEquals($expected, $this->files->get(base_path('composer.testing.json')));
    }

    #[Test]
    public function it_can_add_pre_update_cmd_array_when_doesnt_exist()
    {
        $this->files->put($this->path, <<<'EOT'
{
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi",
            "@php artisan statamic:install --ansi"
        ]
    }
}
EOT
        );

        $this->assertTrue(Json::isMissingPreUpdateCmd());

        Json::addPreUpdateCmd();

        $this->assertFalse(Json::isMissingPreUpdateCmd());

        $expected = <<<'EOT'
{
    "scripts": {
        "pre-update-cmd": [
            "Statamic\\Console\\Composer\\Scripts::preUpdateCmd"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi",
            "@php artisan statamic:install --ansi"
        ]
    }
}
EOT;

        $this->assertEquals($expected, $this->files->get($this->path));
    }

    #[Test]
    public function it_can_add_statamic_pre_update_cmd_to_existing_array()
    {
        $this->files->put($this->path, <<<'EOT'
{
    "scripts": {
        "pre-update-cmd": [
            "Some\\Other\\Package\\Scripts::preUpdateCmd"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi",
            "@php artisan statamic:install --ansi"
        ]
    }
}
EOT
        );

        $this->assertTrue(Json::isMissingPreUpdateCmd());

        Json::addPreUpdateCmd();

        $this->assertFalse(Json::isMissingPreUpdateCmd());

        $expected = <<<'EOT'
{
    "scripts": {
        "pre-update-cmd": [
            "Statamic\\Console\\Composer\\Scripts::preUpdateCmd",
            "Some\\Other\\Package\\Scripts::preUpdateCmd"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi",
            "@php artisan statamic:install --ansi"
        ]
    }
}
EOT;

        $this->assertEquals($expected, $this->files->get($this->path));
    }

    #[Test]
    public function it_does_nothing_if_pre_update_cmd_already_exists()
    {
        $composerJson = <<<'EOT'
{
    "scripts": {
        "pre-update-cmd": [
            "Statamic\\Console\\Composer\\Scripts::preUpdateCmd",
            "Some\\Other\\Package\\Scripts::preUpdateCmd"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi",
            "@php artisan statamic:install --ansi"
        ]
    }
}
EOT;

        $this->files->put($this->path, $composerJson);

        $this->assertFalse(Json::isMissingPreUpdateCmd());

        $attempted = Json::addPreUpdateCmd();

        $this->assertFalse($attempted);
        $this->assertEquals($composerJson, $this->files->get($this->path));
    }

    #[Test]
    public function it_will_throw_error_when_it_unsuccessfully_adds_pre_update_cmd()
    {
        $invalidJson = <<<'EOT'
{
    "scripts": {
        "pre-update-cmd": [
            "Some\\Other\\Package\\Scripts::preUpdateCmd"
}
EOT;

        $this->files->put($this->path, $invalidJson);

        $this->assertEquals($invalidJson, $this->files->get($this->path));

        try {
            Json::addPreUpdateCmd();
        } catch (\Exception $exception) {
            // Catch the exception so we can also assert that it didn't make any changes to the original composer.json file.
        }

        $this->assertEquals('Statamic had trouble adding the `pre-update-cmd` to your composer.json file.', $exception->getMessage());

        $this->assertEquals($invalidJson, $this->files->get($this->path));
    }

    private function backup()
    {
        $this->files->copy($this->path, $this->backupPath);
    }

    private function restore()
    {
        if ($this->files->exists($this->backupPath)) {
            $this->files->copy($this->backupPath, $this->path);
        }
    }
}
