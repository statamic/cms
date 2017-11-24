<?php

namespace Statamic\Data\Services;

class GlobalsService extends BaseService
{
    /**
     * The repo key
     *
     * @var string
     */
    protected $repo = 'globals';

    /**
     * {@inheritdoc}
     * @return \Statamic\Data\Globals\GlobalCollection
     */
    public function all()
    {
        return collect_globals(parent::all());
    }

    /**
     * {@inheritdoc}
     */
    public function handle($handle)
    {
        return $this->repo()->getItem(
            $this->repo()->getIdByPath("globals/{$handle}.yaml")
        );
    }
}