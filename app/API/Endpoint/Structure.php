<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Data\Repositories\StructureRepository;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class Structure
{
    public function all()
    {
        return $this->repo()->all();
    }

    public function find($id)
    {
        return $this->repo()->find($id);
    }

    public function save(StructureContract $structure)
    {
        $this->repo()->save($structure);
    }

    protected function repo()
    {
        return app(StructureRepository::class);
    }
}
