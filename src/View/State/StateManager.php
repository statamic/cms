<?php

namespace Statamic\View\State;

class StateManager
{
    /**
     * A mapping of class names that should be reset later.
     *
     * @var array
     */
    protected static $resetsState = [];

    /**
     * Adds a class name to the internal list.
     *
     * If the provided class name implements the ResetsState
     * interface, that class will have its resetState()
     * method called if another response is generated.
     *
     * @param  string  $class  The class name.
     * @return void
     */
    public static function track($class)
    {
        if (class_implements($class, ResetsState::class)) {
            self::$resetsState[$class] = 1;
        }
    }

    /**
     * Called before a new response is being created.
     *
     * @return void
     */
    public static function resetState()
    {
        if (count(self::$resetsState) > 0) {
            foreach (self::$resetsState as $className => $throwAway) {
                app($className)->resetState();
            }

            self::$resetsState = [];
        }
    }
}
