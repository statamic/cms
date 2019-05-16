<?php

namespace Statamic\Exceptions;

use Whoops\Handler\PrettyPageHandler;
use Illuminate\Foundation\Exceptions\WhoopsHandler as BaseHandler;

class WhoopsHandler extends BaseHandler
{
    protected static $dataTables = [];

    public function forDebug()
    {
        return tap(new PrettyPageHandler, function ($handler) {
            $handler->handleUnconditionally(true);

            $this->registerApplicationPaths($handler)
                 ->registerBlacklist($handler)
                 ->registerEditor($handler)
                 ->registerDataTables($handler);
        });
    }

    public static function addDataTable($name, $values)
    {
        static::$dataTables[$name] = $values;
    }

    protected function registerDataTables($handler)
    {
        foreach (static::$dataTables as $name => $values) {
            $handler->addDataTable($name, $values);
        }

        return $this;
    }
}
