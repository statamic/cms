<?php

namespace Statamic\Forms;

use Carbon\Carbon;
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
use Statamic\Facades\FormSubmission;
use Statamic\Facades\Site as Sites;
use Statamic\Facades\Stache;
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

    protected ?string $redirect = null;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function __clone()
    {
        $this->data = clone $this->data;
        $this->supplements = clone $this->supplements;
    }

    public function data($data = null)
    {
        if (func_num_args() === 0) {
            return $this->traitData();
        }

        $data = collect($data);

        // A full data replacement would otherwise drop the internal lifecycle
        // keys, so carry over the existing partial, spam, and site values
        // unless the incoming payload provides its own.
        foreach (['partial', 'spam', 'site'] as $key) {
            if ($this->has($key) && ! $data->has($key)) {
                $data[$key] = $this->get($key);
            }
        }

        return $this->traitData($data);
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
            return Sites::get($this->get('site')) ?? Sites::default();
        }

        $this->set('site', $site instanceof Site ? $site->handle() : $site);

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
        $this->set('partial', true);

        return $this;
    }

    public function isPartial(): bool
    {
        // Spam submissions aren't in progress, so they shouldn't be resumed.
        // The "partial" key sticks around to indicate that the submission was
        // never finalized, so marking it as not spam can finalize it as normal.
        return $this->get('partial') && ! $this->isSpam();
    }

    public function markAsSpam(): self
    {
        $this->set('spam', true);

        return $this;
    }

    public function markAsNotSpam(): self
    {
        $this->remove('spam');

        return $this;
    }

    public function isSpam(): bool
    {
        return (bool) $this->get('spam');
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
        return collect($uploadedFiles)->map(function ($files, $handle) {
            $field = $this->fields()->get($handle);

            if ($field['type'] === 'form_upload') {
                return FormFileUpload::field($field, $this->id())->upload($files);
            }

            return $field['type'] === 'assets'
                ? AssetsUploader::field($field)->upload($files)
                : FilesUploader::field($field)->upload($files);
        })->all();
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
            if ($isNew) {
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

        $this->remove('partial');

        if ($this->form()->store()) {
            $this->save();
        } else {
            // Fire the created event here for submissions that were never saved.
            // The event might have been dispatched when the submission persisted,
            // so we don't want to fire it again.
            if (is_null($this->form()->submission($this->id()))) {
                SubmissionCreated::dispatch($this);
            }

            $this->deleteQuietly();
        }

        SubmissionFinalized::dispatch($this);

        CreateAssetsFromFileUploads::dispatchSync($this);
        SendEmails::dispatch($this, $this->site());

        return $this;
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
        return $this->data()->all();
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
