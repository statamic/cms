<?php

namespace Statamic\Forms;

use Carbon\Carbon;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Forms\Submission as SubmissionContract;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedData;
use Statamic\Events\SubmissionDeleted;
use Statamic\Events\SubmissionSaved;
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
     * @param Form|null $form
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
     * Get or set the data.
     *
     * @param array|null $data
     * @return array
     */
    public function data($data = null)
    {
        if (func_num_args() === 0) {
            return $this->data;
        }

        $data = collect($data)->intersectByKeys($this->fields())->all();

        $this->data = $data;

        return $this;
    }

    /**
     * Upload files.
     */
    public function uploadFiles()
    {
        collect($this->fields())
            ->filter(function ($config, $handle) {
                return Arr::get($config, 'type') === 'assets' && request()->hasFile($handle);
            })
            ->each(function ($config, $handle) {
                Arr::set($this->data, $handle, AssetsUploader::field($config)->upload(request()->file($handle)));
            });

        return $this;
    }

    /**
     * Whether the submissin has the given key.
     *
     * @return bool
     */
    public function has($field)
    {
        return array_has($this->data(), $field);
    }

    /**
     * Get a value of a field.
     *
     * @param  string $key
     * @return mixed
     */
    public function get($field)
    {
        return array_get($this->data(), $field);
    }

    /**
     * Set a value of a field.
     *
     * @param string $field
     * @param mixed  $value
     * @return void
     */
    public function set($field, $value)
    {
        array_set($this->data, $field, $value);
    }

    /**
     * Save the submission.
     */
    public function save()
    {
        File::put($this->getPath(), YAML::dump($this->data()));

        SubmissionSaved::dispatch($this);
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
                return in_array($key, ['id', 'date']);
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
        return $this->toArray();
    }

    public function blueprint()
    {
        return $this->form->blueprint();
    }
}
