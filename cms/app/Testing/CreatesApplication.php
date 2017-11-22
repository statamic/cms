<?php

namespace Statamic\Testing;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require $this->bootstrapAppFile();

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
