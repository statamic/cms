<?php

namespace Statamic\Contracts\Forms;

use Statamic\Contracts\CP\Editable;
use Illuminate\Contracts\Support\Arrayable;

interface Form extends Arrayable, Editable
{
    /**
     * Get or set the Formset
     *
     * @param  Formset|null $formset
     * @return Formset
     */
    public function formset($formset = null);

    /**
     * Get the submissions
     *
     * @return Illuminate\Support\Collection
     */
    public function submissions();

    /**
     * Get a submission
     *
     * @param  string $id
     * @return Submission
     */
    public function submission($id);

    /**
     * Create a submission
     *
     * @return Submission
     */
    public function createSubmission();

    /**
     * Delete a submission
     *
     * @return boolean
     */
    public function deleteSubmission($id);

    /**
     * Get or set the name
     *
     * @param  string|null $name
     * @return string
     */
    public function name($name = null);

    /**
     * Get or set the title
     *
     * @param  string|null $title
     * @return string
     */
    public function title($title = null);

    /**
     * Get or set the honeypot field
     *
     * @param  string|null $honeypot
     * @return string
     */
    public function honeypot($honeypot = null);

    /**
     * Get or set the fields
     *
     * @param  array|null $fields
     * @return array
     */
    public function fields($fields = null);

    /**
     * Get or set the columns
     *
     * @param  array|null $columns
     * @return array
     */
    public function columns($columns = null);

    /**
     * Get all the metrics
     *
     * @param array|null $metrics
     * @return array
     */
    public function metrics($metrics = null);

    /**
     * Get or set the email config
     *
     * @param  array|null $email
     * @return array
     */
    public function email($email = null);

    /**
     * Save the form
     *
     * @return void
     */
    public function save();
}
