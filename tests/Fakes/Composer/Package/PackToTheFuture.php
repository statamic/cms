<?php

namespace Tests\Fakes\Composer\Package;

use Illuminate\Support\Facades\File;

class PackToTheFuture
{
    /**
     * Set version on our test package.
     *
     * @param string $version
     */
    public static function setVersion(string $version)
    {
        File::put(base_path('tests/Fakes/Package/test-package/composer.json'), json_encode([
            'name' => 'test/package',
            'version' => $version,
        ], JSON_UNESCAPED_SLASHES));
    }
}
