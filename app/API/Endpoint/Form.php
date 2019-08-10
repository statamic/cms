<?php

namespace Statamic\API\Endpoint;

use Request as Req;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Folder;
use Statamic\API\Metrics;
use Statamic\Contracts\Forms\Form as FormContract;

class Form
{
    /**
     * Find a form.
     *
     * @param string $handle
     * @return FormContract
     */
    public function find($handle)
    {
        $form = $this->make($handle);

        if (! File::exists($form->path())) {
            return;
        }

        return $form->hydrate();
    }

    /**
     * Get all forms.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return collect(Folder::getFilesByType(config('statamic.forms.forms'), 'yaml'))->map(function ($file) {
            return self::find(pathinfo($file)['filename']);
        });
    }

    /**
     * Make form instance.
     *
     * @param mixed $handle
     * @return FormContract
     */
    public function make($handle = null)
    {
        $form = app(FormContract::class);

        if ($handle) {
            $form->handle($handle);
        }

        return $form;
    }
}
