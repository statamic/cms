<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        $path = now()->timestamp.'/'.$file->getClientOriginalName();

        Storage::disk('local')->putFileAs('statamic/file-uploads', $file, $path);

        return ['data' => ['id' => $path]];
    }
}
