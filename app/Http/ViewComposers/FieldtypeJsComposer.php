<?php

namespace Statamic\Http\ViewComposers;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Addon;
use Statamic\API\Config;
use Illuminate\Contracts\View\View;
use Statamic\Extend\Management\AddonRepository;

class FieldtypeJsComposer
{
    /**
     * @var AddonRepository
     */
    private $repo;

    public function compose(View $view)
    {
        $view->with('fieldtype_js', $this->fieldtypeJs());
    }

    private function fieldtypeJs()
    {
        // Don't bother doing anything on the login screen.
        if (\Route::current() && \Route::current()->getName() === 'login') {
            return '';
        }

        $defaults = [];

        $str = '';

        foreach (app('statamic.fieldtypes') as $fieldtype) {
            $fieldtype = app($fieldtype);
            $defaults[$fieldtype->getHandle()] = $fieldtype->blank();
        }

        return '<script>Statamic.fieldtypeDefaults = '.json_encode($defaults).';</script>' . $str . $this->redactor();
    }

    private function redactor()
    {
        $str = '<script>Statamic.redactorSettings = ';

        $configs = collect(Config::get('statamic.system.redactor', []))->keyBy('name')->map(function ($config) {
            return $config['settings'];
        })->all();

        $str .= json_encode($configs);

        $str .= ';</script>';

        return $str;
    }
}
