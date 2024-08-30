<?php

namespace Statamic\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Statamic\Actions\ActionRepository;

/**
 * @method static \Statamic\Actions\Action get($action)
 * @method static Collection all()
 * @method static Collection for($item, $context = [])
 * @method static Collection forBulk($items, $context = [])
 *
 * @see \Statamic\Actions\ActionRepository
 */
class Action extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ActionRepository::class;
    }
}
