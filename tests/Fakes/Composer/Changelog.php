<?php

namespace Tests\Fakes\Composer;

use Facades\Statamic\Console\Processes\Composer;

class Changelog
{
    protected $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public static function product(string $slug)
    {
        return new static($slug);
    }

    public function currentVersion()
    {
        return Composer::installedVersion($this->slug);
    }

    public function composerPackage()
    {
        //
    }

    public function get()
    {
        return collect(['1.1.2', '1.1.1', '1.1.0', '1.0.1', '1.0.0'])
            ->mapWithKeys(function ($version) {
                return [$version => $this->releasePayload($version)];
            });
    }

    public function latest()
    {
        return $this->get()->first();
    }

    private function releasePayload($version)
    {
        return (object) [
            'version' => $version,
            'type' => 'upgrade',
            'latest' => true,
            'date' => 'September 14th, 2018',
            'body' => 'Release notes.',
        ];
    }
}
