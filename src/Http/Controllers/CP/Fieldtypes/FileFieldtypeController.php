<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Facades\File;
use Statamic\Http\Controllers\CP\CpController;

class FileFieldtypeController extends CpController
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

        $path = now()->timestamp.'/'.$file->getClientOriginalName();

        $stream = fopen($file->getRealPath(), 'r');

        File::disk()->put(
            storage_path('statamic/file-uploads/').$path,
            $stream,
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        return ['data' => ['id' => $path]];
    }
}
