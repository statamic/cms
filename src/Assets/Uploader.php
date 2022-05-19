<?php

namespace Statamic\Assets;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Statamic\Facades\Path;
use Statamic\Support\Str;
use Stringy\Stringy;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{
    private $asset;
    private $files;

    /**
     * Instantiate asset uploader.
     *
     * @param  Asset  $asset
     */
    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
        $this->files = app(Filesystem::class);
    }

    /**
     * Instantiate asset uploader.
     *
     * @param  Asset  $asset
     * @return static
     */
    public static function asset(Asset $asset)
    {
        return new static($asset);
    }

    /**
     * Upload file to asset's container.
     *
     * @param  UploadedFile  $file
     * @return string
     */
    public function upload(UploadedFile $file)
    {
        $path = $this->getSafeUploadPath($file);

        $glide = $this->asset->container()->glide();

        $sourcePath = $glide
            ? $this->glideProcessUploadedFile($file, $glide)
            : $file->getRealPath();

        $this->putFileOnDisk($sourcePath, $path);

        if ($glide) {
            $this->glideClearTmpCache();
        }

        return $path;
    }

    /**
     * Get the container's filesystem disk instance.
     *
     * @return \Statamic\Filesystem\FlysystemAdapter
     */
    private function disk()
    {
        return $this->asset->container()->disk();
    }

    /**
     * Get safe upload path for UploadedFile.
     *
     * @param  UploadedFile  $file
     * @return string
     */
    private function getSafeUploadPath(UploadedFile $file)
    {
        $ext = $file->getClientOriginalExtension();
        $filename = $this->getSafeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

        $directory = $this->asset->folder();
        $directory = ($directory === '.') ? '/' : $directory;
        $path = Path::tidy($directory.'/'.$filename.'.'.$ext);
        $path = ltrim($path, '/');

        // If the file exists, we'll append a timestamp to prevent overwriting.
        if ($this->disk()->exists($path)) {
            $basename = $filename.'-'.Carbon::now()->timestamp.'.'.$ext;
            $path = Str::removeLeft(Path::assemble($directory, $basename), '/');
        }

        return $path;
    }

    /**
     * Get safe filename.
     *
     * @param  string  $string
     * @return string
     */
    private function getSafeFilename($string)
    {
        $replacements = [
            ' ' => '-',
            '#' => '-',
        ];

        $str = Stringy::create(urldecode($string))->toAscii();

        foreach ($replacements as $from => $to) {
            $str = $str->replace($from, $to);
        }

        return (string) $str;
    }

    /**
     * Put file on destination disk.
     *
     * @param  string  $sourcePath
     * @param  string  $destinationPath
     */
    private function putFileOnDisk($sourcePath, $destinationPath)
    {
        $stream = fopen($sourcePath, 'r');

        $this->disk()->put($destinationPath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    /**
     * Get temporary glide cache path in storage for processing uploads.
     *
     * @return string
     */
    private function glideTmpPath()
    {
        return storage_path('statamic/glide/tmp');
    }

    /**
     * Process UploadedFile instance using glide and return cached path.
     *
     * @param  UploadedFile  $file
     * @param  array  $params
     * @param string
     */
    private function glideProcessUploadedFile(UploadedFile $file, $params)
    {
        $glideTmpPath = $this->glideTmpPath();

        $server = \League\Glide\ServerFactory::create([
            'source' => $file->getPath(),
            'cache' => $glideTmpPath,
            'driver' => config('statamic.assets.image_manipulation.driver'),
            'cache_with_file_extensions' => true,
        ]);

        $server->makeImage($file->getFilename(), $params);

        $newFilePath = collect($this->files->files($glideTmpPath.'/'.$file->getFilename()))
            ->first()
            ->getRealPath();

        return $newFilePath;
    }

    /**
     * Clear tmp glide cache.
     */
    private function glideClearTmpCache()
    {
        $glideTmpPath = $this->glideTmpPath();

        if ($this->files->exists($glideTmpPath)) {
            $this->files->deleteDirectory($glideTmpPath);
        }
    }
}
