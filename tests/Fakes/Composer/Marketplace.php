<?php

namespace Tests\Fakes\Composer;

class Marketplace
{
    public function approvedAddons()
    {
        return [
            'data' => [
                $this->addonPayload('addon/one'),
                $this->addonPayload('addon/two'),
                $this->addonPayload('addon/three'),
            ]
        ];
    }

    private function addonPayload($repo)
    {
        return [
            'variants' => [
                ['githubRepo' => $repo]
            ]
        ];
    }
}
