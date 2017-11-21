<?php

namespace Statamic\Contracts\Forms;

use Statamic\Contracts\CP\Editable;
use Illuminate\Contracts\Support\Arrayable;

interface Formset extends Arrayable, Editable
{
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
     * Get or set the data
     *
     * @param  array|null $data
     * @return array
     */
    public function data($data = null);

    /**
     * Get a value in the formset
     *
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Set a value in the formset
     *
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function set($key, $value);

    /**
     * Save the formset
     *
     * @return void
     */
    public function save();
}
