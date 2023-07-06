<?php

namespace Statamic\Forms;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Contracts\Forms\Submission;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Events\FormBlueprintFound;
use Statamic\Events\FormCreated;
use Statamic\Events\FormDeleted;
use Statamic\Events\FormSaved;
use Statamic\Events\FormSaving;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\YAML;
use Statamic\Forms\Exceptions\BlueprintUndefinedException;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Form implements FormContract, Augmentable, Arrayable
{
    use FluentlyGetsAndSets, HasAugmentedInstance;

    protected $handle;
    protected $title;
    protected $blueprint;
    protected $honeypot;
    protected $store;
    protected $email;
    protected $metrics;
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;

    /**
     * Get or set the handle.
     *
     * @param  mixed  $handle
     * @return mixed
     */
    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    /**
     * Get or set the title.
     *
     * @param  mixed  $title
     * @return mixed
     */
    public function title($title = null)
    {
        return $this->fluentlyGetOrSet('title')->args(func_get_args());
    }

    /**
     * Get the blueprint.
     *
     * @return mixed
     */
    public function blueprint()
    {
        $blueprint = Blueprint::find('forms.'.$this->handle())
            ?? Blueprint::makeFromFields([])->setHandle($this->handle())->setNamespace('forms');

        FormBlueprintFound::dispatch($blueprint, $this);

        return $blueprint;
    }

    /**
     * Get or set the honeypot field.
     *
     * @param  mixed  $honeypot
     * @return mixed
     */
    public function honeypot($honeypot = null)
    {
        return $this->fluentlyGetOrSet('honeypot')
            ->getter(function ($honeypot) {
                return $honeypot ?? 'honeypot';
            })
            ->setter(function ($honeypot) {
                return $honeypot === 'honeypot' ? null : $honeypot;
            })
            ->args(func_get_args());
    }

    /**
     * Get or set the store field.
     *
     * @param  mixed  $store
     * @return mixed
     */
    public function store($store = null)
    {
        return $this->fluentlyGetOrSet('store')
            ->getter(function ($store) {
                return $store !== false;
            })
            ->setter(function ($store) {
                return $store === false ? false : null;
            })
            ->args(func_get_args());
    }

    /**
     * Get or set the email field.
     *
     * @param  mixed  $emails
     * @return mixed
     */
    public function email($emails = null)
    {
        return $this->fluentlyGetOrSet('email')->args(func_get_args());
    }

    /**
     * Get the form fields off the blueprint.
     *
     * @return \Illuminate\Support\Collection
     */
    public function fields()
    {
        if (! $blueprint = $this->blueprint()) {
            throw BlueprintUndefinedException::create($this);
        }

        return $blueprint->fields()->all();
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function path()
    {
        return config('statamic.forms.forms')."/{$this->handle()}.yaml";
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
     * Save form.
     */
    public function save()
    {
        $isNew = is_null(FormFacade::find($this->handle()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if (FormSaving::dispatch($this) === false) {
                return false;
            }
        }

        $data = collect([
            'title' => $this->title,
            'honeypot' => $this->honeypot,
            'email' => collect(isset($this->email['to']) ? [$this->email] : $this->email)->map(function ($email) {
                $email['markdown'] = Arr::get($email, 'markdown') === true ? true : null;
                $email['attachments'] = Arr::get($email, 'attachments') === true ? true : null;

                return Arr::removeNullValues($email);
            })->all(),
            'metrics' => $this->metrics,
        ])->filter()->all();

        if ($this->store === false) {
            $data['store'] = false;
        }

        File::put($this->path(), YAML::dump($data));

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                FormCreated::dispatch($this);
            }

            FormSaved::dispatch($this);
        }
    }

    /**
     * Delete form and associated submissions.
     */
    public function delete()
    {
        $this->submissions()->each->delete();

        File::delete($this->path());

        FormDeleted::dispatch($this);
    }

    /**
     * Hydrate form from file data.
     *
     * @return $this
     */
    public function hydrate()
    {
        collect(YAML::parse(File::get($this->path())))
            ->filter(function ($value, $property) {
                return in_array($property, [
                    'title',
                    'honeypot',
                    'store',
                    'email',
                ]);
            })
            ->each(function ($value, $property) {
                $this->{$property}($value);
            });

        return $this;
    }

    // TODO: Reimplement metrics()
    public function metrics($metrics = null)
    {
        return collect();

        // if (! is_null($metrics)) {
        //     return $this->formset()->set('metrics', $metrics);
        // }

        // $metrics = [];

        // foreach ($this->formset()->get('metrics', []) as $config) {
        //     $name = Str::studly($config['type']);

        //     $class = "Statamic\\Forms\\Metrics\\{$name}Metric";

        //     if (! class_exists($class)) {
        //         $class = "Statamic\\Addons\\{$name}\\{$name}Metric";
        //     }

        //     if (! class_exists($class)) {
        //         \Log::error("Metric [{$config['type']}] does not exist.");
        //         continue;
        //     }

        //     $metrics[] = new $class($this, $config);
        // }

        // return $metrics;
    }

    /**
     * Get the submissions.
     *
     * @return \Illuminate\Support\Collection
     */
    public function submissions()
    {
        $path = config('statamic.forms.submissions').'/'.$this->handle();

        return collect(Folder::getFilesByType($path, 'yaml'))->map(function ($file) {
            return $this->makeSubmission()
                ->id(pathinfo($file)['filename'])
                ->data(YAML::parse(File::get($file)));
        });
    }

    /**
     * Get a submission.
     *
     * @param  string  $id
     * @return Submission
     */
    public function submission($id)
    {
        return $this->submissions()->filter(function ($submission) use ($id) {
            return $submission->id() === $id;
        })->first();
    }

    /**
     * Make a form submission.
     *
     * @return Submission
     */
    public function makeSubmission()
    {
        $submission = app(Submission::class);

        $submission->form($this);

        return $submission;
    }

    /**
     * The URL to view form submissions in the CP.
     *
     * @return string
     */
    public function showUrl()
    {
        return cp_route('forms.show', $this->handle());
    }

    /**
     * The URL to edit this in the CP.
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('forms.edit', $this->handle());
    }

    /**
     * The URL to delete this in the CP.
     *
     * @return string
     */
    public function deleteUrl()
    {
        return cp_route('forms.destroy', $this->handle());
    }

    /**
     * Get the date format.
     *
     * @return string
     */
    public function dateFormat()
    {
        return Statamic::isCpRoute() ? Statamic::cpDateTimeFormat() : Statamic::dateTimeFormat();
    }

    public function hasFiles()
    {
        return $this->fields()->filter(function ($field) {
            return $field->fieldtype()->handle() === 'assets';
        })->isNotEmpty();
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedForm($this);
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['handle', 'title', 'api_url'];
    }

    public function apiUrl()
    {
        return Statamic::apiRoute('forms.show', $this->handle());
    }

    /**
     * Get the form action url.
     *
     * @return string
     */
    public function actionUrl()
    {
        return route('statamic.forms.submit', $this->handle());
    }
}
