<?php

namespace Statamic\Forms;

use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Forms\Submission as SubmissionContract;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedData;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Events\SubmissionCreated;
use Statamic\Events\SubmissionCreating;
use Statamic\Events\SubmissionDeleted;
use Statamic\Events\SubmissionFinalized;
use Statamic\Events\SubmissionSaved;
use Statamic\Events\SubmissionSaving;
use Statamic\Facades\File;
use Statamic\Facades\FormConnector;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\Site as Sites;
use Statamic\Facades\Stache;
use Statamic\Fields\Field;
use Statamic\Forms\Connectors\RecordConnectionSuccess;
use Statamic\Forms\Uploaders\AssetsUploader;
use Statamic\Forms\Uploaders\FilesUploader;
use Statamic\Forms\Uploaders\FormFileUpload;
use Statamic\Sites\Site;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Submission implements Augmentable, ContainsQueryableValues, SubmissionContract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedData, TracksQueriedColumns, TracksQueriedRelations {
        data as traitData;
    }

    /**
     * @var string
     */
    private $id;

    /**
     * @var Form
     */
    public $form;

    protected $afterSaveCallbacks = [];
    protected $withEvents = true;
    protected $finalizing = false;

    protected ?string $redirect = null;

    protected $internal;

    public function __construct()
    {
        $this->data = collect();
        $this->internal = collect();
        $this->supplements = collect();
    }

    public function __clone()
    {
        $this->data = clone $this->data;
        $this->internal = clone $this->internal;
        $this->supplements = clone $this->supplements;
    }

    public function data($data = null)
    {
        if (func_num_args() === 0) {
            return $this->traitData();
        }

        [$internal, $data] = collect($data)->partition(fn ($value, $key) => $this->isInternalKey($key));

        // A full data replacement would otherwise drop the internal lifecycle
        // keys, so carry over the existing _partial, _marked_spam_at and _site values
        // unless the incoming payload provides its own.
        $this->internal = $this->internal->only(['_partial', '_marked_spam_at', '_site'])->merge($internal);

        return $this->traitData($data);
    }

    public function get($key, $fallback = null)
    {
        return ($this->isInternalKey($key) ? $this->internal : $this->data)->get($key, $fallback);
    }

    public function has($key)
    {
        return ($this->isInternalKey($key) ? $this->internal : $this->data)->has($key);
    }

    public function set($key, $value)
    {
        ($this->isInternalKey($key) ? $this->internal : $this->data)->put($key, $value);

        return $this;
    }

    public function remove($key)
    {
        ($this->isInternalKey($key) ? $this->internal : $this->data)->forget($key);

        return $this;
    }

    public function merge($data)
    {
        collect($data)->each(fn ($value, $key) => $this->set($key, $value));

        return $this;
    }

    private function isInternalKey($key): bool
    {
        return is_string($key) && Str::startsWith($key, '_');
    }

    public function values()
    {
        return $this->data();
    }

    /**
     * Get or set the ID.
     *
     * @param mixed|null
     * @return mixed
     */
    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')
            ->getter(function ($id) {
                $micro = Carbon::now()->timestamp + Carbon::now()->micro / 1000000;

                return $this->id = $id ?: str_replace(',', '.', $micro);
            })
            ->args(func_get_args());
    }

    /**
     * Get or set the form.
     *
     * @param  Form|null  $form
     * @return Form
     */
    public function form($form = null)
    {
        return $this->fluentlyGetOrSet('form')->args(func_get_args());
    }

    /**
     * Get or set the site.
     *
     * @return Site|$this
     */
    public function site(Site|string|null $site = null): Site|static
    {
        if (func_num_args() === 0) {
            return Sites::get($this->get('_site')) ?? Sites::default();
        }

        $this->set('_site', $site instanceof Site ? $site->handle() : $site);

        return $this;
    }

    /**
     * Get the form fields.
     *
     * @return \Illuminate\Support\Collection<string, array>
     */
    public function fields()
    {
        return $this->form()->fields()->map->toArray();
    }

    /**
     * Get the columns.
     *
     * @return \Statamic\CP\Columns
     */
    public function columns()
    {
        return $this->form()->blueprint()->columns();
    }

    /**
     * Get the date when this was submitted.
     *
     * @return Carbon
     */
    public function date()
    {
        return Carbon::createFromTimestamp($this->id());
    }

    public function asPartial(): self
    {
        $this->set('_partial', true);

        return $this;
    }

    public function isPartial(): bool
    {
        // Spam submissions aren't in progress, so they shouldn't be resumed.
        // The "_partial" key sticks around to indicate that the submission was
        // never finalized, so marking it as not spam can finalize it as normal.
        return $this->get('_partial') && ! $this->isSpam();
    }

    public function markAsSpam(): self
    {
        if (! $this->isSpam()) {
            $this->set('_marked_spam_at', now()->timestamp);
        }

        return $this;
    }

    public function markAsNotSpam(): self
    {
        $this->remove('_marked_spam_at');

        return $this;
    }

    public function isSpam(): bool
    {
        return $this->get('_marked_spam_at') !== null;
    }

    public function status(): string
    {
        return match (true) {
            $this->isSpam() => 'spam',
            $this->isPartial() => 'partial',
            default => 'finalized',
        };
    }

    /**
     * Upload files and return their storage references.
     *
     * @param  array  $uploadedFiles
     * @return array
     */
    public function uploadFiles($uploadedFiles)
    {
        $fields = $this->fields();

        return collect($uploadedFiles)
            ->filter(fn ($files, $handle) => in_array($fields->get($handle)['type'] ?? null, ['assets', 'files', 'form_upload']))
            ->map(fn ($files, $handle) => match ($fields->get($handle)['type']) {
                'form_upload' => FormFileUpload::field($fields->get($handle), $this->id())->upload($files),
                'assets' => AssetsUploader::field($fields->get($handle))->upload($files),
                'files' => FilesUploader::field($fields->get($handle))->upload($files),
            })
            ->all();
    }

    public function afterSave($callback)
    {
        $this->afterSaveCallbacks[] = $callback;

        return $this;
    }

    public function saveQuietly()
    {
        $this->withEvents = false;

        return $this->save();
    }

    /**
     * Save the submission.
     */
    public function save()
    {
        $isNew = is_null($this->form()->submission($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $finalizing = $this->finalizing;
        $this->finalizing = false;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && SubmissionCreating::dispatch($this) === false) {
                return false;
            }

            if (SubmissionSaving::dispatch($this) === false) {
                return false;
            }
        }

        FormSubmission::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($finalizing || ($isNew && ! $this->get('_partial'))) {
                SubmissionCreated::dispatch($this);
            }

            SubmissionSaved::dispatch($this);
        }
    }

    public function finalize()
    {
        if (! $this->isPartial()) {
            return $this;
        }

        $this->remove('_partial');

        // Assets need to exist before anything reads the submission, so this stays synchronous.
        CreateAssetsFromFileUploads::dispatchSync($this);

        // Partials don't fire the created event when they're persisted, so it fires when they're finalized instead.
        if ($this->form()->store()) {
            $this->finalizing = true;
            $this->save();
        } else {
            SubmissionCreated::dispatch($this);

            $this->deleteQuietly();
        }

        SubmissionFinalized::dispatch($this);

        // Jobs that can't report success are treated as done once dispatched, so they go
        // first and the temporary files they may need survive until they've been handed off.
        [$countable, $uncountable] = collect($this->connectionJobs())
            ->partition(fn ($job) => RecordConnectionSuccess::isAttachable($job))
            ->map->values()
            ->map->all();

        $this->dispatchConnectionJobs($uncountable);

        if ($this->shouldDeleteTemporaryFiles()) {
            if ($countable) {
                RecordConnectionSuccess::countDown($this, $countable);
            } else {
                rescue(fn () => Bus::dispatch(new DeleteTemporaryFiles($this)));
            }
        }

        $this->dispatchConnectionJobs($countable);

        return $this;
    }

    private function dispatchConnectionJobs(array $jobs): void
    {
        // Dispatched independently so one failing connection (e.g. on the sync queue) doesn't stop the others.
        foreach ($jobs as $job) {
            rescue(fn () => Bus::dispatch($job));
        }
    }

    public function connectionJobs(): array
    {
        return $this->form()->connections()
            ->map(fn ($connections, $handle) => FormConnector::find($handle)?->setForm($this->form())->setConnections($connections)->finalized($this))
            ->flatten()
            ->filter()
            ->values()
            ->all();
    }

    private function shouldDeleteTemporaryFiles(): bool
    {
        return $this->form()->blueprint()->fields()->all()
            ->contains(fn (Field $field) => in_array($field->type(), ['files', 'form_upload']));
    }

    public function deleteQuietly()
    {
        $this->withEvents = false;

        return $this->delete();
    }

    /**
     * Delete this submission.
     */
    public function delete()
    {
        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents) {
            DeleteTemporaryFiles::dispatchSync($this);
        }

        FormSubmission::delete($this);

        if ($withEvents) {
            SubmissionDeleted::dispatch($this);
        }

        return true;
    }

    /**
     * Get the path to the file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path();
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.yaml', [
            rtrim(Stache::store('form-submissions')->directory(), '/'),
            $this->form()->handle(),
            $this->id(),
        ]);
    }

    /**
     * Convert to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $data = $this->data();

        return $this->form()->fields()->keys()->flip()
            ->reject(function ($field, $key) {
                return in_array($key, ['id', 'date', 'form']);
            })
            ->map(function ($field, $key) use ($data) {
                return $data[$key] ?? null;
            })
            ->merge([
                'id' => $this->id(),
                'date' => $this->date(),
            ])
            ->all();
    }

    public function augmentedArrayData()
    {
        return array_merge($this->toArray(), [
            'form' => $this->form,
        ]);
    }

    public function blueprint()
    {
        return $this->form->blueprint();
    }

    public function fileData()
    {
        return $this->data->merge($this->internal)->all();
    }

    public function getQueryableValue(string $field)
    {
        if ($field === 'status') {
            return null;
        }

        if (in_array($method = Str::camel($field), $this->queryableMethods())) {
            return $this->{$method}();
        }

        $value = $this->get($field);

        if (! $field = $this->blueprint()->field($field)) {
            return $value;
        }

        return $field->fieldtype()->toQueryableValue($value);
    }

    private function queryableMethods(): array
    {
        return [
            'blueprint', 'date', 'form', 'formattedDate', 'id', 'path', 'site',
        ];
    }

    public function __get($key)
    {
        return $this->get($key);
    }
}
