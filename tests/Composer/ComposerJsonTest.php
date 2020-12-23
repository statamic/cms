<?php

namespace Tests\Composer;

use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Composer\Json;
use Statamic\Console\Composer\Scripts;
use Tests\TestCase;

class ComposerJsonTest extends TestCase
{
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

    /** @test */
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
