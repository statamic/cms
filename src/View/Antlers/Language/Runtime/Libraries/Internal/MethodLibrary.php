<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class MethodLibrary extends RuntimeLibrary
{
    protected $name = 'method';
    protected $isRuntimeProtected = true;

    public function __construct()
    {
        $this->exposedMethods = [
            'call' => [
                $this->anyParam('callback'),
                [
                    self::KEY_NAME => 'args',
                    self::KEY_ACCEPTS_MANY => true,
                    self::KEY_HAS_DEFAULT => true,
                    self::KEY_DEFAULT => null,
                    self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY, self::KEY_TYPE_OBJECT, self::KEY_TYPE_NUMERIC, self::KEY_TYPE_STRING, self::KEY_TYPE_BOOL],
                ],
            ],
        ];
    }

    public function call($callback, $args)
    {
        if (is_string($callback)) {
            [$class, $method] = Str::parseCallback($callback);

            $instance = app($class);

            if (count($args) == 0) {
                if (method_exists($instance, $method)) {
                    return $instance->$method();
                }
            }

            return call_user_func_array([$instance, $method], $args);
        } elseif (is_object($callback)) {
            $method = array_shift($args);

            return call_user_func_array([$callback, $method], $args);
        }

        return null;
    }
}
