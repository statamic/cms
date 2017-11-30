<?php

namespace Statamic\Forms;

use Statamic\API\Str;
use Statamic\API\YAML;
use Statamic\API\File;
use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\Exceptions\FatalException;
use Statamic\Contracts\Forms\Form as FormContract;

class Form implements FormContract
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var Formset
     */
    private $formset;

    /**
     * Get the Formset
     *
     * @return Formset
     * @throws FatalException
     */
    public function formset($formset = null)
    {
        if (is_null($formset)) {
            return $this->formset;
        }

        if (! $formset instanceof Formset) {
            throw new FatalException('A Formset instance is required.');
        }

        $this->formset = $formset;
    }

    /**
     * Get the submissions
     *
     * @return Illuminate\Support\Collection
     */
    public function submissions()
    {
        $submissions = collect();

        $files = Folder::disk('storage')->getFilesByType("forms/{$this->name()}", 'yaml');

        foreach ($files as $file) {
            $submission = $this->createSubmission();
            $submission->id(pathinfo($file)['filename']);
            $submission->unguard();
            $submission->data(YAML::parse(File::disk('storage')->get($file)));

            $submissions->push($submission);
        }

        return $submissions;
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
     * Create a form submission
     *
     * @return Statamic\Contracts\Forms\Submission
     */
    public function createSubmission()
    {
        $submission = app('Statamic\Contracts\Forms\Submission');

        $submission->form($this);

        return $submission;
    }

    /**
     * Delete a form submission
     *
     * @return void
     */
    public function deleteSubmission($id)
    {
        $submission = $this->submission($id);

        $submission->delete();
    }

    /**
     * Get or set the name
     *
     * @param  string|null $name
     * @return string
     */
    public function name($name = null)
    {
        if (is_null($name)) {
            return $this->name;
        }

        $this->name = $name;
    }

    /**
     * Get or set the title
     *
     * @param  string|null $title
     * @return string
     */
    public function title($title = null)
    {
        return $this->formset()->title($title);
    }

    /**
     * Get or set the fields
     *
     * @param  array|null $fields
     * @return array
     */
    public function fields($fields = null)
    {
        return $this->formset()->fields($fields);
    }

    /**
     * Get or set the columns
     *
     * @param  array|null $columns
     * @return array
     */
    public function columns($columns = null)
    {
        return $this->formset()->columns($columns);
    }

    /**
     * Save the form
     */
    public function save()
    {
        $this->formset()->name($this->name());

        $this->formset()->save();
    }

    /**
     * Get the date format
     *
     * @return string
     */
    public function dateFormat()
    {
        return $this->formset()->get('date_format', Config::get('cp.date_format'));
    }

    /**
     * Get or set the metrics
     *
     * @param array|null $metrics
     * @return array
     */
    public function metrics($metrics = null)
    {
        if (! is_null($metrics)) {
            return $this->formset()->set('metrics', $metrics);
        }

        $metrics = [];

        foreach ($this->formset()->get('metrics', []) as $config) {
            $name = Str::studly($config['type']);

            $class = "Statamic\\Forms\\Metrics\\{$name}Metric";

            if (! class_exists($class)) {
                $class = "Statamic\\Addons\\{$name}\\{$name}Metric";
            }

            if (! class_exists($class)) {
                \Log::error("Metric [{$config['type']}] does not exist.");
                continue;
            }

            $metrics[] = new $class($this, $config);
        }

        return $metrics;
    }

    /**
     * Get or set the email config
     *
     * @param  array|null $email
     * @return array
     */
    public function email($email = null)
    {
        if (is_null($email)) {
            return $this->formset()->get('email', []);
        }

        $this->formset()->set('email', $email);
    }

    /**
     * Get or set the honeypot field
     *
     * @param string|null $honeypot
     * @return string
     */
    public function honeypot($honeypot = null)
    {
        if (is_null($honeypot)) {
            return $this->formset()->get('honeypot', 'honeypot');
        }

        $honeypot = ($honeypot === 'honeypot') ? null : $honeypot;

        $this->formset()->set('honeypot', $honeypot);
    }

    /**
     * The URL to view submissions in the CP
     *
     * @return string
     */
    public function url()
    {
        return $this->formset()->url();
    }

    /**
     * The URL to edit this in the CP
     *
     * @return string
     */
    public function editUrl()
    {
        return $this->formset()->editUrl();
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name(),
            'title' => $this->title(),
            'edit_url' => $this->editUrl()
        ];
    }
}
