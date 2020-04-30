<?php

namespace Statamic\Forms\Exporters;

class JsonExporter extends AbstractExporter
{
    /**
     * Perform the export.
     *
     * @return string
     */
    public function export()
    {
        $submissions = $this->form()->submissions()->toArray();

        return json_encode($submissions);
    }

    /**
     * Get the content type.
     *
     * @return string
     */
    public function contentType()
    {
        return 'application/json';
    }
}
