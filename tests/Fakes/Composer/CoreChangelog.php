<?php

namespace Tests\Fakes\Composer;

class CoreChangelog
{
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
