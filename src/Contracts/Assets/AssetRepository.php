<?php

namespace Statamic\Contracts\Assets;

interface AssetRepository
{
    public function all();

    public function whereContainer(string $container);

    public function whereFolder(string $folder, string $container);

    public function find(string $asset);

    public function findByUrl(string $url);

    public function findById(string $id);

    public function findByPath(string $path);

    public function make();

    public function query();

    public function save($asset);
}
