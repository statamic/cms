<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Image;
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

        $this->putFileOnDisk($sourcePath, $path);

        return ['data' => ['id' => $path]];
    }

    private function glideProcessUploadedFile(UploadedFile $file, $preset)
    {
        $server = ServerFactory::create([
            'source' => $file->getPath(),
            'cache' => $this->glideTmpPath,
            'driver' => config('statamic.assets.image_manipulation.driver'),
            'watermarks' => public_path(),
            'presets' => Image::manipulationPresets(),
        ]);

        try {
            return $this->glideTmpPath.'/'.$server->makeImage($file->getFilename(), ['p' => $preset]);
        } catch (\Exception $exception) {
            return $file->getRealPath();
        }
    }

    private function putFileOnDisk($sourcePath, $destinationPath)
    {
        $stream = fopen($sourcePath, 'r');

        Storage::disk('local')->put('statamic/file-uploads/'.$destinationPath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }
}
