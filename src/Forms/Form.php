<?php

namespace Statamic\Forms;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Contracts\Forms\Submission;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Events\FormBlueprintFound;
use Statamic\Events\FormDeleted;
use Statamic\Events\FormSaved;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Facades\YAML;
use Statamic\Forms\Exceptions\BlueprintUndefinedException;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Form implements FormContract, Augmentable
{
    use FluentlyGetsAndSets, HasAugmentedInstance;

    protected $handle;
    protected $title;
    protected $blueprint;
    protected $honeypot;
    protected $store;
    protected $email;
    protected $metrics;

    /**
     * Get or set the handle.
     *
     * @param mixed $handle
     * @return mixed
     */
    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    /**
     * Get or set the title.
     *
     * @param mixed $title
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
     * @param mixed $honeypot
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
     * @param mixed $store
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
     * @param mixed $emails
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

    /**
     * Save form.
     */
    public function save()
    {
        $data = collect([
            'title' => $this->title,
            'honeypot' => $this->honeypot,
            'email' => collect($this->email)->map(function ($email) {
                return Arr::removeNullValues($email);
            })->all(),
            'metrics' => $this->metrics,
        ])->filter()->all();

        if ($this->store === false) {
            $data['store'] = false;
        }

        File::put($this->path(), YAML::dump($data));

        FormSaved::dispatch($this);
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
     * @param  string $id
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
        // TODO: Should this be a form.yaml config, a config/forms.php config, or a global config?
        return 'M j, Y @ H:i';

        // return $this->formset()->get('date_format', 'M j, Y @ h:i');
    }

    /**
     * Convert to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'handle' => $this->handle,
            'title' => $this->title,
            'honeypot' => $this->honeypot(),
            'store' => $this->store(),
            'email' => $this->email,
        ];
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

    protected function shallowAugmentedArrayKeys()
    {
        return ['handle', 'title'];
    }
}
