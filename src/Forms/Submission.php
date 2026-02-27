<?php

namespace Statamic\Forms;

use Carbon\Carbon;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Forms\Submission as SubmissionContract;
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
use Statamic\Facades\Stache;
use Statamic\Forms\Uploaders\AssetsUploader;
use Statamic\Forms\Uploaders\FilesUploader;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Submission implements Augmentable, SubmissionContract
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
                return $this->id = $id ?: str_replace(',', '.', microtime(true));
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
     * @return array
     */
    public function fields()
    {
        return $this->form()->fields()->map->toArray();
    }

    /**
     * Get or set the columns.
     *
     * @return array
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
        return Carbon::createFromTimestamp($this->id(), config('app.timezone'));
    }

    /**
     * Get the date, formatted by what's specified in the form config.
     *
     * @return string
     */
    public function formattedDate()
    {
        return $this->date()->format(
            $this->form()->dateFormat()
        );
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

    public function __get($key)
    {
        return $this->get($key);
    }
}
