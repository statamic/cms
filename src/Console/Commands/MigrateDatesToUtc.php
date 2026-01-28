<?php

namespace Statamic\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Date as DateFieldtype;
use Statamic\Listeners\Concerns\GetsItemsContainingData;
use Statamic\Support\Arr;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;

class MigrateDatesToUtc extends Command
{
    use GetsItemsContainingData, RunsInPlease;

    private ?string $currentTimezone;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:migrate-dates-to-utc
        { timezone : Specify the timezone your dates are currently saved in }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates dates in your content from your current timezone to UTC.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->currentTimezone = $this->argument('timezone');

        $this->components->warn('This command makes changes to content. Please make a backup before running.');
        $this->components->info('This operation converts content dates to UTC for storage purposes only. System functionality is identical whether you proceed or not – do so only if specifically desired.');

        if (! confirm('Do you want to continue', default: false)) {
            return;
        }

        $items = $this->getItemsContainingData();

        if ($items->isEmpty()) {
            $this->components->warn('No content found. Exiting.');

            return;
        }

        $progress = progress(
            label: 'Converting dates to UTC',
            steps: $items->count(),
            hint: 'This may take a while depending on the amount of content you have.'
        );

        $progress->start();

        $items->each(function ($item) use ($progress) {
            $progress->advance();

            /** @var Fields $fields */
            $fields = $item->blueprint()->fields();

            $this->recursivelyUpdateFields($item, $fields);

            if (method_exists($item, 'isDirty')) {
                if ($item->isDirty()) {
                    $item->saveQuietly();
                }
            } else {
                $item->saveQuietly();
            }

            $progress->advance();
        });

        $progress->finish();

        $this->components->info("Migrated dates from [{$this->currentTimezone}] to [UTC].");

        $this->components->bulletList([
            'You may now safely change your application\'s timezone to UTC',
            "If you're storing content in a database, or outside of version control, you will need to run this command after deploying",
        ]);
    }

    private function recursivelyUpdateFields($item, Fields $fields, ?string $dottedPrefix = null): void
    {
        $this
            ->updateDateFields($item, $fields, $dottedPrefix)
            ->updateDateFieldsInGroups($item, $fields, $dottedPrefix)
            ->updateDateFieldsInGrids($item, $fields, $dottedPrefix)
            ->updateDateFieldsInReplicators($item, $fields, $dottedPrefix)
            ->updateDateFieldsInBard($item, $fields, $dottedPrefix);
    }

    private function updateDateFields($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'date')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                if (
                    $item instanceof EntryContract
                    && $item->collection()->dated()
                    && empty($dottedPrefix)
                    && $field->handle() === 'date'
                ) {
                    // When entries are constructed, the datestamp from the filename would be provided but treated as UTC.
                    // We need them to be adjusted back to the existing timezone.
                    $item->date(Carbon::createFromFormat(
                        $format = 'Y-m-d H:i:s',
                        $item->date()->format($format),
                        $this->currentTimezone
                    ));

                    return;
                }

                $data = $item->data()->all();

                $dottedKey = $dottedPrefix.$field->handle();

                if (! Arr::has($data, $dottedKey)) {
                    return;
                }

                $value = Arr::get($data, $dottedKey);

                $value = $field->get('mode') === 'range'
                    ? $this->processRange($value, $field)
                    : $this->processSingle($value, $field);

                Arr::set($data, $dottedKey, $value);

                $item->data($data);
            });

        return $this;
    }

    private function updateDateFieldsInGroups($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'group')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                $dottedKey = "{$dottedPrefix}{$field->handle()}";

                $this->updateDateFields($item, $field->fieldtype()->fields(), $dottedKey.'.');
            });

        return $this;
    }

    private function updateDateFieldsInGrids($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'grid')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                $data = $item->data();
                $dottedKey = "{$dottedPrefix}{$field->handle()}";

                $rows = Arr::get($data, $dottedKey, []);

                collect($rows)->each(function ($set, $setKey) use ($item, $dottedKey, $field) {
                    $dottedPrefix = "{$dottedKey}.{$setKey}.";
                    $fields = Arr::get($field->config(), 'fields');

                    if ($fields) {
                        $this->recursivelyUpdateFields($item, new Fields($fields), $dottedPrefix);
                    }
                });
            });

        return $this;
    }

    private function updateDateFieldsInReplicators($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'replicator')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                $data = $item->data();
                $dottedKey = "{$dottedPrefix}{$field->handle()}";

                $sets = Arr::get($data, $dottedKey);

                collect($sets)->each(function ($set, $setKey) use ($item, $dottedKey, $field) {
                    $dottedPrefix = "{$dottedKey}.{$setKey}.";
                    $setHandle = Arr::get($set, 'type');
                    $fields = Arr::get($field->fieldtype()->flattenedSetsConfig(), "{$setHandle}.fields");

                    if ($setHandle && $fields) {
                        $this->recursivelyUpdateFields($item, new Fields($fields), $dottedPrefix);
                    }
                });
            });

        return $this;
    }

    private function updateDateFieldsInBard($item, Fields $fields, ?string $dottedPrefix = null): self
    {
        $fields->all()
            ->filter(fn (Field $field) => $field->type() === 'bard')
            ->each(function (Field $field) use ($item, $dottedPrefix) {
                $data = $item->data();
                $dottedKey = "{$dottedPrefix}{$field->handle()}";

                $sets = Arr::get($data, $dottedKey);

                collect($sets)->each(function ($set, $setKey) use ($item, $dottedKey, $field) {
                    $dottedPrefix = "{$dottedKey}.{$setKey}.attrs.values.";
                    $setHandle = Arr::get($set, 'attrs.values.type');
                    $fields = Arr::get($field->fieldtype()->flattenedSetsConfig(), "{$setHandle}.fields");

                    if ($setHandle && $fields) {
                        $this->recursivelyUpdateFields($item, new Fields($fields), $dottedPrefix);
                    }
                });
            });

        return $this;
    }

    private function processRange(string|array $value, Field $field): array
    {
        if (! is_array($value)) {
            $value = ['start' => $value, 'end' => $value];
        }

        return [
            'start' => $this->processSingle($value['start'], $field),
            'end' => $this->processSingle($value['end'], $field),
        ];
    }

    private function processSingle(int|string $value, Field $field): int|string
    {
        $value = Carbon::parse($value, $this->currentTimezone)
            ->utc()
            ->format($field->get('format', $this->defaultFormat($field)));

        if (is_numeric($value)) {
            $value = (int) $value;
        }

        return $value;
    }

    private function defaultFormat(Field $field): string
    {
        return $field->get('time_seconds_enabled')
            ? DateFieldtype::DEFAULT_DATETIME_WITH_SECONDS_FORMAT
            : DateFieldtype::DEFAULT_DATETIME_FORMAT;
    }
}
