<?php

namespace Statamic\Contracts\Forms;

use Illuminate\Contracts\Support\Arrayable;

interface Submission extends Arrayable
{
    /**
     * Get or set the ID
     *
     * @param mixed|null
     * @return mixed
     */
    public function id($id = null);

    /**
     * Get or set the form
     *
     * @param Form|null $form
     * @return Form
     */
    public function form($form = null);

    /**
     * Get the formset
     *
     * @return Formset
     */
    public function formset();

    /**
     * Get the fields in the formset
     *
     * @return array
     */
    public function fields();

    /**
     * Get the columns
     *
     * @return array
     */
    public function columns();

    /**
     * Get the date when this was submitted
     *
     * @return Carbon
     */
    public function date();

    /**
     * Get or set the data
     *
     * @param array|null $data
     * @return array
     */
    public function data($data = null);

    /**
     * Get a value of a field
     *
     * @param  string $key
     * @return mixed
     */
    public function get($field);

    /**
     * Set a value of a field
     *
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function set($field, $value);

    /**
     * Delete sybmission
     *
     * @param  string $key
     * @return mixed
     */
    public function delete();

    /**
     * Save the submission
     *
     * @return void
     */
    public function save();
}
