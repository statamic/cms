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
use Statamic\Events\SubmissionSaved;
use Statamic\Events\SubmissionSaving;
use Statamic\Facades\Asset;
use Statamic\Facades\File;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Forms\Uploaders\AssetsUploader;
use Statamic\Forms\Uploaders\FilesUploader;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Submission implements Augmentable, ContainsQueryableValues, SubmissionContract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedData, TracksQueriedColumns, TracksQueriedRelations;

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

    public function isIncomplete(): bool
    {
        return (bool) $this->get('incomplete');
    }

    public function isSpam(): bool
    {
        return (bool) $this->get('spam');
    }

    public function status(): string
    {
        return match (true) {
            $this->isSpam() => 'spam',
            $this->isIncomplete() => 'incomplete',
            default => 'complete',
        };
    }

    /**
     * Upload files and return asset IDs.
     *
     * @param  array  $uploadedFiles
     * @return array
     */
    public function uploadFiles($uploadedFiles)
    {
        return collect($uploadedFiles)->map(function ($files, $handle) {
            $field = $this->fields()->get($handle);

            return $field['type'] === 'files'
                ? FilesUploader::field($field)->upload($files)
                : AssetsUploader::field($field)->upload($files);
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

        // Incomplete and spam submissions are stored but skip the Creating/Created
        // events so listeners never receive an incomplete submission.
        $isWithheld = $this->isIncomplete() || $this->isSpam();

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && ! $isWithheld && SubmissionCreating::dispatch($this) === false) {
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
            if ($isNew && ! $isWithheld) {
                SubmissionCreated::dispatch($this);
            }

            SubmissionSaved::dispatch($this);
        }
    }

    public function complete()
    {
        $existed = ! is_null($this->form()->submission($this->id()));

        $this->remove('incomplete')->remove('spam');

        if ($this->form()->store()) {
            $this->save();

            // A promoted incomplete already existed, so save() won't fire the created
            // event. We dispatch it here so completion always emits it once.
            if ($existed) {
                SubmissionCreated::dispatch($this);
            }
        } else {
            SubmissionCreated::dispatch($this);
        }

        // TODO: Use $this->site() here when we add the "site" key to submissions.
        SendEmails::dispatch($this, Site::default());
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
            'blueprint', 'date', 'form', 'formattedDate', 'id', 'path',
        ];
    }

    public function __get($key)
    {
        return $this->get($key);
    }
}
