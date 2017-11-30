<?php

namespace Statamic\Forms\Metrics;

use Statamic\API\Str;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Metric;

abstract class AbstractMetric implements Metric
{
    /**
     * @var Form
     */
    private $form;

    /**
     * @var array
     */
    private $config;

    /**
     * Create a new Metric
     *
     * @param Form $form
     * @param array $config
     */
    public function __construct(Form $form, $config = [])
    {
        $this->form = $form;
        $this->config = $config;
    }

    /**
     * Get the config
     *
     * @return array
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Get a value from the config
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_get($this->config(), $key, $default);
    }

    /**
     * Get the form
     *
     * @return Form
     */
    public function form()
    {
        return $this->form;
    }

    /**
     * Get the form submissions
     *
     * @return Illuminate\Support\Collection
     */
    public function submissions()
    {
        return $this->form()->submissions();
    }

    /**
     * Get the metric label
     *
     * @return string
     */
    public function label()
    {
        return $this->get('label', Str::title($this->get('type')));
    }

    /**
     * Get the metric result
     *
     * @return mixed
     */
    abstract public function result();
}
