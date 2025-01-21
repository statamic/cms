<?php

namespace Tests\Composer;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Composer\Json;
use Statamic\Console\Composer\Scripts;
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
