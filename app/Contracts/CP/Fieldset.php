<?php

namespace Statamic\Contracts\CP;

use Illuminate\Contracts\Support\Arrayable;

interface Fieldset extends Arrayable, Editable
{
    /**
     * Get or set the type
     *
     * @param string|null $type
     * @return mixed
     */
    public function type($type = null);

    /**
     * Get or set the locale
     *
     * @param string|null $locale
     * @return mixed
     */
    public function locale($locale = null);

    /**
     * Get the path to the file
     *
     * @return string
     */
    public function path();

    /**
     * Get or set the name
     *
     * @param string|null $name
     * @return mixed
     */
    public function name($name = null);

    /**
     * Get or set the title
     *
     * @param string|null $title
     * @return mixed
     */
    public function title($title = null);

    /**
     * Get or set whether this fieldset is hidden from the selection dialog
     *
     * @param  bool|null $hidden
     * @return bool
     */
    public function hidden($hidden = null);

    /**
     * Get or set the contents
     *
     * @param array|null $contents
     * @return mixed
     */
    public function contents($contents = null);

    /**
     * Get the fields
     *
     * @return array
     */
    public function fields();

    /**
     * Get the fieldtypes
     *
     * @return \Statamic\Extend\Fieldtype[]
     */
    public function fieldtypes();

    /**
     * Get or set the taxonomies
     *
     * @param array|null $taxonomies
     * @return mixed
     */
    public function taxonomies($taxonomies = null);

    /**
     * Save the fieldset
     *
     * @return mixed
     */
    public function save();

    /**
     * Delete the fieldset
     *
     * @return mixed
     */
    public function delete();
}
