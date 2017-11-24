<?php

namespace Statamic\Contracts\Forms;

interface Metric
{
    /**
     * Get the config
     *
     * @return array
     */
    public function config();

    /**
     * Get a value from the config
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Get the form
     *
     * @return Form
     */
    public function form();

    /**
     * Get the form submissions
     *
     * @return Illuminate\Support\Collection
     */
    public function submissions();

    /**
     * Get the metric label
     *
     * @return string
     */
    public function label();

    /**
     * Get the metric result
     *
     * @return mixed
     */
    public function result();
}
