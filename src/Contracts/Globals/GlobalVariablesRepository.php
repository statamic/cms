<?php

namespace Statamic\Contracts\Globals;

use Statamic\Globals\VariablesCollection;

interface GlobalVariablesRepository
{
    public function all(): VariablesCollection;

    public function find($id): ?Variables;

    public function findOrFail($id): Variables;

    public function whereSet($handle): VariablesCollection;

    public function save($variable);

    public function delete($variable);
}
