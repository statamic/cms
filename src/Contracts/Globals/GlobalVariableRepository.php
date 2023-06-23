<?php

namespace Statamic\Contracts\Globals;

use Statamic\Globals\VariableCollection;

interface GlobalVariableRepository
{
    public function all(): VariableCollection;

    public function find($id): ?Variables;

    public function findBySet($handle): ?VariableCollection;

    public function save($variable);

    public function delete($variable);
}
