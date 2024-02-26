<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Assets\FileUploader as Uploader;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Validation\AllowedFile;

class FilesFieldtypeController extends CpController
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['file', new AllowedFile],
        ]);

        $file = $request->file('file');

        $path = Uploader::container($request->container)->upload($file);

        return ['data' => ['id' => $path]];
    }
}
