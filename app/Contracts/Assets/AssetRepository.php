<?php

namespace Statamic\Contracts\Assets;

use Statamic\Assets\AssetCollection;

interface AssetRepository
{
    public function all(): AssetCollection;
    public function whereContainer(string $container): AssetCollection;
    public function whereFolder(string $folder, string $container): AssetCollection;
    public function find(string $asset): ?Asset;
    public function findByUrl(string $url): ?Asset;
    public function findById(string $id): ?Asset;
    public function findByPath(string $path): ?Asset;
    public function make(): Asset;
    public function query();
    public function save(Asset $asset);
}
