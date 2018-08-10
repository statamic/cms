<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Data\Repositories\StructureRepository;


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

    protected function repo()
    {
        return app(StructureRepository::class);
    }
}
