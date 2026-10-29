<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Fieldtype;
use Tests\TestCase;

class FieldtypesListTest extends TestCase
{
    #[Test]
    public function it_lists_registered_fieldtypes_without_evaluating_their_configuration()
    {
        app('statamic.fieldtypes')->put('test_listed', ListedFieldtype::class);
        app('statamic.fieldtypes')->put('test_unconfigured', UnconfiguredFieldtype::class);

        $this->assertSame(0, Artisan::call('statamic:fieldtypes:list', ['--json' => true]));

        $data = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
        $handles = array_column($data['fieldtypes'], 'handle');

        $this->assertSame('3.0.0-testing', $data['statamic_version']);
        $this->assertContains('bard', $handles);
        $this->assertContains('markdown', $handles);
        $this->assertContains('test_listed', $handles);
        $this->assertContains('test_unconfigured', $handles);

        $sorted = $handles;
        sort($sorted);
        $this->assertSame($sorted, $handles);

        $fieldtype = collect($data['fieldtypes'])->firstWhere('handle', 'test_listed');
        $this->assertSame(ListedFieldtype::class, $fieldtype['class']);
        $this->assertSame('<fg=red>Listed</>', $fieldtype['title']);
        $this->assertFalse($fieldtype['selectable']);
    }

    #[Test]
    public function it_can_list_fieldtypes_without_json()
    {
        $this->artisan('statamic:fieldtypes:list')
            ->expectsOutputToContain('bard')
            ->expectsOutputToContain('markdown')
            ->assertSuccessful();
    }

    #[Test]
    public function it_can_run_through_please()
    {
        require_once __DIR__.'/../Kernel.php';

        $kernel = app(\Statamic\Console\Please\Kernel::class);

        $this->assertSame(0, $kernel->call('fieldtypes:list', ['--json' => true]));
        $this->assertNotEmpty(json_decode($kernel->output(), true, 512, JSON_THROW_ON_ERROR)['fieldtypes']);
    }
}

class ListedFieldtype extends Fieldtype
{
    protected static $title = '<fg=red>Listed</>';

    protected $selectable = false;
}

class UnconfiguredFieldtype extends Fieldtype
{
    protected function configFieldItems(): array
    {
        throw new \RuntimeException('Listing must not evaluate configuration.');
    }
}
