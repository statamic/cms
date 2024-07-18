<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Actions\ActionRepository;

/**
 * @method static mixed get($action)
 * @method static mixed all()
 * @method static mixed for($item, $context = [])
 * @method static mixed forBulk($items, $context = [])
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
