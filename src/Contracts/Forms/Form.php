<?php

namespace Statamic\Contracts\Forms;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Fields\Blueprint;

interface Form extends Arrayable
{
    /**
     * Get or set the handle.
     *
     * @param  string|null $name
     * @return string
     */
    public function handle($handle = null);

    /**
     * Get or set the title.
     *
     * @param  string|null $title
     * @return string
     */
    public function title($title = null);

    /**
     * Get or set the blueprint.
     *
     * @param mixed $blueprint
     * @return mixed
     */
    public function blueprint($blueprint = null);

    /**
     * Get the submissions.
     *
     * @return Illuminate\Support\Collection
     */
    public function submissions();

    /**
     * Get a submission.
     *
     * @param  string $id
     * @return Submission
     */
    public function submission($id);

    /**
     * Make a submission.
     *
     * @return Submission
     */
    public function makeSubmission();

    /**
     * Delete a submission.
     *
     * @return bool
     */
    public function deleteSubmission($id);

    /**
     * Get or set the honeypot field.
     *
     * @param  string|null $honeypot
     * @return string
     */
    public function honeypot($honeypot = null);

    /**
     * Get all the metrics.
     *
     * @param array|null $metrics
     * @return array
     */
    public function metrics($metrics = null);

    /**
     * Get or set the email config.
     *
     * @param  array|null $email
     * @return array
     */
    public function email($email = null);

    /**
     * Save the form.
     *
     * @return void
     */
    public function save();
}
