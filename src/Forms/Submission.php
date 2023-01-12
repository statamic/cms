<?php

namespace Statamic\Forms;

use Carbon\Carbon;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Forms\Submission as SubmissionContract;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedData;
use Statamic\Events\SubmissionCreated;
use Statamic\Events\SubmissionDeleted;
use Statamic\Events\SubmissionSaved;
use Statamic\Events\SubmissionSaving;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Forms\Uploaders\AssetsUploader;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Submission implements SubmissionContract, Augmentable
{
    use ContainsData, FluentlyGetsAndSets, HasAugmentedData;

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

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
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
        return Carbon::createFromTimestamp($this->id());
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
            return AssetsUploader::field($this->fields()->get($handle))->upload($files);
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
            if (SubmissionSaving::dispatch($this) === false) {
                return false;
            }
        }

        File::put($this->getPath(), YAML::dump(Arr::removeNullValues($this->data()->all())));

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

    /**
     * Delete this submission.
     */
    public function delete()
    {
        File::delete($this->getPath());

        SubmissionDeleted::dispatch($this);
    }

    /**
     * Get the path to the file.
     *
     * @return string
     */
    public function getPath()
    {
        return config('statamic.forms.submissions').'/'.$this->form()->handle().'/'.$this->id().'.yaml';
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

    public function __get($key)
    {
        return $this->get($key);
    }
}
