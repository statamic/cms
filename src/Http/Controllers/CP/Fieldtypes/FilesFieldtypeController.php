<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use Statamic\Facades\AssetContainer;
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
            if ($preset = AssetContainer::find($request->container)->glideSourcePreset()) {
                $params = config("statamic.assets.image_manipulation.presets.{$preset}", []);
            }

            $sourcePath = $preset && $params
                ? $this->glideProcessUploadedFile($file, $params)
                : $sourcePath;
        }

        $this->putFileOnDisk($sourcePath, $path);

        return ['data' => ['id' => $path]];
    }

    private function glideProcessUploadedFile(UploadedFile $file, $params)
    {
        $server = ServerFactory::create([
            'source' => $file->getPath(),
            'cache' => $this->glideTmpPath,
            'driver' => config('statamic.assets.image_manipulation.driver'),
            'watermarks' => public_path(),
        ]);

        try {
            return $this->glideTmpPath.'/'.$server->makeImage($file->getFilename(), $params);
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
