<?php

namespace Statamic\Contracts\Data\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\AssetContainer;

interface AssetContainerRepository
{
    public function all(): Collection;
    public function find($id): ?AssetContainer;
    public function findByHandle(string $handle): ?AssetContainer;
    public function make(string $handle = null): AssetContainer;
}
