<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\File;
use Symfony\Component\VarExporter\VarExporter;

class AddonEditionsController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure addons');
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'addon' => 'required',
            'edition' => 'required',
        ]);

        $config = config('statamic.editions');
        $config['addons'][$request->addon] = $request->edition;

        $str = '<?php return '.VarExporter::export($config).';';

        File::put(config_path('statamic/editions.php'), $str);
    }
}
