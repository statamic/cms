<?php

namespace Statamic\Forms\Exporters;

use Statamic\Contracts\Forms\Exporter;

abstract class AbstractExporter implements Exporter
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Statamic\Contracts\Forms\Form
     */
    private $form;

    /**
     * Get or set the config.
     *
     * @param  array|null  $config
     * @return array|void
     */
    public function config($config = null)
    {
        if (is_null($config)) {
            return $this->$config;
        }

        $this->config = $config;
    }

    /**
     * Get or set the form.
     *
     * @param  Statamic\Contracts\Forms\Form|null  $form
     * @return Statamic\Contracts\Forms\Form|void
     */
    public function form($form = null)
    {
        if (is_null($form)) {
            return $this->form;
        }

        $this->form = $form;
    }

    /**
     * Get the content type.
     *
     * @return string
     */
    public function contentType()
    {
        return 'text/plain';
    }
}
