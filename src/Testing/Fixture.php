<?php

declare(strict_types=1);

namespace Statamic\Testing;

use Illuminate\Support\Facades\Storage;

class Fixture
{
    /**
     * Get the file path to a fixture.
     *
     * @param  string  $file
     * @return string
     */
    public static function path($file)
    {
        return Storage::build([
            'driver' => 'local',
            'root' => implode(DIRECTORY_SEPARATOR, [
                __DIR__, '../../tests/__fixtures__',
            ]),
        ])->path($file);
    }
}
