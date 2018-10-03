<?php

namespace Tests\Fakes\Composer;

class Marketplace
{
    public function approvedAddons()
    {
        return collect([
            'addon/one',
            'addon/two',
            'addon/three',
        ]);
    }
}
