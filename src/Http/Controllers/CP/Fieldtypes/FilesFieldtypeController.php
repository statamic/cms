<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Glide;
use Statamic\Http\Controllers\CP\CpController;

class FilesFieldtypeController extends CpController
{
    public function upload(Request $request)
    {
        $this->glideTmpPath = storage_path('statamic/glide/tmp');

        $request->validate([
            'file' => ['file', function ($attribute, $value, $fail) {
                if (in_array(trim(strtolower($value->getClientOriginalExtension())), ['php', 'php3', 'php4', 'php5', 'phtml'])) {
                    $fail(__('validation.uploaded'));
                }
            }],
        ]);

        $file = $request->file('file');

        $path = now()->timestamp.'/'.$file->getClientOriginalName();

        $sourcePath = $file->getRealPath();

        if ($request->container) {
            $preset = AssetContainer::find($request->container)->glideSourcePreset();

            $sourcePath = $preset
                ? $this->glideProcessUploadedFile($file, $preset)
                : $sourcePath;
        }

        $this->putFileOnDisk(Storage::disk('local'), $sourcePath, 'statamic/file-uploads/'.$path);

        return ['data' => ['id' => $path]];
    }

    private function glideProcessUploadedFile(UploadedFile $file, $preset)
    {
        $server = Glide::server([
            'source' => $file->getPath(),
            'cache' => $this->glideTmpPath,
            'cache_with_file_extensions' => false,
        ]);

        try {
            return $this->glideTmpPath.'/'.$server->makeImage($file->getFilename(), ['p' => $preset]);
        } catch (\Exception $exception) {
            return $file->getRealPath();
        }
    }

    private function putFileOnDisk($disk, $sourcePath, $destinationPath)
    {
        $stream = fopen($sourcePath, 'r');

        $disk->put($destinationPath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }
}
