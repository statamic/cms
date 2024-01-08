<?php

namespace Statamic\Contracts\Assets;

use Illuminate\Support\Collection;

interface AssetContainerRepository
{
    public function all(): Collection;

    public function find($id): ?AssetContainer;

    public function findByHandle(string $handle): ?AssetContainer;

    public function make(string $handle = null): AssetContainer;
}
