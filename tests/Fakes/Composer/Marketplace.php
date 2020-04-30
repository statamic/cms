<?php

namespace Tests\Fakes\Composer;

class Marketplace
{
    public function query()
    {
        return $this;
    }

    public function withoutLocalData()
    {
        return $this;
    }

    public function filter($filter)
    {
        return $this;
    }

    public function search($filter)
    {
        return $this;
    }

    public function get()
    {
        return [
            'data' => [
                $this->addonPayload('addon/one'),
                $this->addonPayload('addon/two'),
                $this->addonPayload('addon/three'),
            ],
        ];
    }

    public function show($addon)
    {
        return [
            'data' => $this->addonPayload($addon),
        ];
    }

    public function paginate()
    {
        return $this->get();
    }

    private function addonPayload($repo)
    {
        return [
            'variants' => [
                ['package' => $repo],
            ],
        ];
    }
}
