<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstallEloquentDriverTest extends TestCase
{
    private $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        Composer::shouldReceive('isInstalled')->with('statamic/eloquent-driver')->andReturn(true);

        Process::fake();
    }

    public function tearDown(): void
    {
        $this->files->delete(config_path('statamic/eloquent-driver.php'));

        parent::tearDown();
    }

    #[Test]
    public function it_switches_to_the_uuid_entry_model_when_importing_entries()
    {
        $this->writeConfig(<<<'PHP'
<?php

use Statamic\Eloquent\Entries\Entry;
use Statamic\Eloquent\Entries\EntryModel;

return [
    'entries' => [
        'driver' => 'file',
        'model' => EntryModel::class,
        'entry' => Entry::class,
    ],
];
PHP);

        $this->artisan('statamic:install:eloquent-driver', ['--repositories' => 'entries', '--import' => true]);

        $this->assertEquals(<<<'PHP'
<?php

use Statamic\Eloquent\Entries\Entry;
use Statamic\Eloquent\Entries\UuidEntryModel;

return [
    'entries' => [
        'driver' => 'eloquent',
        'model' => UuidEntryModel::class,
        'entry' => Entry::class,
    ],
];
PHP, $this->files->get(config_path('statamic/eloquent-driver.php')));

        Process::assertRan(fn ($process) => in_array('--tag=statamic-eloquent-entries-table-with-string-ids', $process->command));
        Process::assertRan(fn ($process) => in_array('statamic:eloquent:import-entries', $process->command));
    }

    #[Test]
    public function it_switches_to_the_uuid_entry_model_when_importing_entries_with_fully_qualified_class_names_in_config()
    {
        $this->writeConfig(<<<'PHP'
<?php

return [
    'entries' => [
        'driver' => 'file',
        'model' => \Statamic\Eloquent\Entries\EntryModel::class,
        'entry' => \Statamic\Eloquent\Entries\Entry::class,
    ],
];
PHP);

        $this->artisan('statamic:install:eloquent-driver', ['--repositories' => 'entries', '--import' => true]);

        $this->assertEquals(<<<'PHP'
<?php

return [
    'entries' => [
        'driver' => 'eloquent',
        'model' => \Statamic\Eloquent\Entries\UuidEntryModel::class,
        'entry' => \Statamic\Eloquent\Entries\Entry::class,
    ],
];
PHP, $this->files->get(config_path('statamic/eloquent-driver.php')));
    }

    private function writeConfig(string $contents): void
    {
        $this->files->ensureDirectoryExists(config_path('statamic'));
        $this->files->put(config_path('statamic/eloquent-driver.php'), $contents);
    }
}
