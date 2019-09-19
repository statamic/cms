<?php

namespace Statamic\Contracts\Forms;

interface Exporter
{
    /**
     * Perform the export
     *
     * @return string
     */
    public function export();

    /**
     * Get or set the form
     *
     * @param  Statamic\Contracts\Forms\Form|null $form
     * @return Statamic\Contracts\Forms\Form
     */
    public function form($form = null);

    /**
     * Get the content type
     *
     * @return string
     */
    public function contentType();
}
