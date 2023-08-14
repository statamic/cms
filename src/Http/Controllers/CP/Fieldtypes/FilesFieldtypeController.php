<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Assets\FileUploader as Uploader;
use Statamic\Http\Controllers\CP\CpController;

class FilesFieldtypeController extends CpController
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['file', function ($attribute, $value, $fail) {
                if (in_array(trim(strtolower($value->getClientOriginalExtension())), ['php', 'php3', 'php4', 'php5', 'phtml'])) {
                    $fail(__('validation.uploaded'));
                }
            }],
        ]);

        $file = $request->file('file');

        $path = Uploader::container($request->container)->upload($file);

        return ['data' => ['id' => $path]];
    }
}
