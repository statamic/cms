<?php

namespace Statamic\Forms\Exporters;

use Statamic\Contracts\Forms\Exporter;

abstract class AbstractExporter implements Exporter
{
    /**
     * @var array
     */
    public $config;

    /**
     * @var Statamic\Contracts\Forms\Form
     */
    private $form;

    /**
     * @var string
     */
    protected static $title;

    /**
     * Create the exporter
     *
     * @param  array  $config
     * @return self
     */
    public function __construct($config = [])
    {
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

    /**
     * Get the extension.
     *
     * @return string
     */
    public function extension()
    {
        return 'txt';
    }

    /**
     * Get the title.
     *
     * @return string
     */
    public static function title()
    {
        return __(static::$title);
    }
}
