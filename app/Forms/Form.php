<?php

namespace Statamic\Forms;

use Statamic\API;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\CP\Column;
use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\Fields\Blueprint;
use Statamic\FluentlyGetsAndSets;
use Statamic\Exceptions\FatalException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Contracts\Forms\Form as FormContract;

class Form implements FormContract
{
    use FluentlyGetsAndSets;

    protected $handle;
    protected $title;
    protected $blueprint;
    protected $honeypot;
    protected $store;
    protected $email;

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
     * Get or set the blueprint.
     *
     * @param mixed $blueprint
     * @return mixed
     */
    public function blueprint($blueprint = null)
    {
        return $this->fluentlyGetOrSet('blueprint')
            ->getter(function ($blueprint) {
                return API\Blueprint::find($blueprint);
            })
            ->setter(function ($blueprint) {
                return $blueprint instanceof Blueprint ? $blueprint->handle() : $blueprint;
            })
            ->args(func_get_args());
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
                return (bool) $store ?? true;
            })
            ->setter(function ($store) {
                return $store === false ? false : null;
            })
            ->args(func_get_args());
    }

    /**
     * Get or set the email field.
     *
     * @param mixed $email
     * @return mixed
     */
    public function email($email = null)
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
        return $this->blueprint()->fields()->all();
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function path()
    {
        return config('statamic.forms.forms') . "/{$this->handle()}.yaml";
    }

    /**
     * Save form.
     */
    public function save()
    {
        $data = collect([
            'title' => $this->title,
            'blueprint' => $this->blueprint,
            'honeypot' => $this->honeypot,
            // 'metrics' => $this->get('metrics'),
            // 'email' => $this->get('email')
        ])->filter()->all();

        if ($this->store === false) {
            $data['store'] = false;
        }

        File::put($this->path(), YAML::dump($data));
    }

    /**
     * Delete form and associated submissions.
     */
    public function delete()
    {
        $this->submissions()->each->delete();

        File::delete($this->path());
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
                    'blueprint',
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
     * Get the submissions
     *
     * @return \Illuminate\Support\Collection
     */
    public function submissions()
    {
        $path = config('statamic.forms.submissions') . '/' . $this->handle();

        return collect(Folder::getFilesByType($path, 'yaml'))->map(function ($file) {
            return $this->createSubmission()
                ->id(pathinfo($file)['filename'])
                ->unguard()
                ->data(YAML::parse(File::get($file)));
        });
    }

    /**
     * Get a submission
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
     * Create a form submission.
     *
     * @return Submission
     */
    public function createSubmission()
    {
        $submission = app(Submission::class);

        $submission->form($this);

        return $submission;
    }

    /**
     * Delete a form submission.
     */
    public function deleteSubmission($id)
    {
        $submission = $this->submission($id);

        $submission->delete();
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
     * Is a field an uploadable type?
     *
     * @param string $field
     * @return mixed
     */
    public function isUploadableField($field)
    {
        // TODO: Reimplement isUploadableField()
        return false;

        // $field = collect($this->fields())->get($field);

        // return in_array(array_get($field, 'type'), ['file', 'files', 'asset', 'assets']);
    }

    /**
     * Get the date format.
     *
     * @return string
     */
    public function dateFormat()
    {
        // TODO: Should this be a form.yaml config, a config/forms.php config, or a global config?
        return 'M j, Y @ h:m';

        // return $this->formset()->get('date_format', 'M j, Y @ h:m');
    }

    public function sanitize()
    {
        // TODO: This was a form.yaml config option?
        // ie. formset()->get('sanitize', true)
        return true;
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
            'blueprint' => $this->blueprint,
            'honeypot' => $this->honeypot(),
            'store' => $this->store,
            'email' => $this->email,
        ];
    }
}
