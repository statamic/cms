<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static self file($file)
 * @method static array parse($str = null)
 * @method static string dump($data, $content = null)
 * @method static string dumpFrontMatter($data, $content = null)
 *
 * @see \Statamic\Yaml\Yaml
 */
class YAML extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Yaml\Yaml::class;
    }
}
