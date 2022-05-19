<?php

namespace Statamic\Assets;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use League\Glide\ServerFactory;
use Statamic\Facades\Path;
use Statamic\Support\Str;
use Stringy\Stringy;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{
    private $asset;
    private $files;
    private $glideTmpPath;

    /**
     * Instantiate asset uploader.
     *
     * @param  Asset  $asset
     */
    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
        $this->files = app(Filesystem::class);
        $this->glideTmpPath = storage_path('statamic/glide/tmp');
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
     * Process UploadedFile instance using glide and return cached path.
     *
     * @param  UploadedFile  $file
     * @param  array  $params
     * @param string
     */
    private function glideProcessUploadedFile(UploadedFile $file, $params)
    {
        $server = ServerFactory::create([
            'source' => $file->getPath(),
            'cache' => $this->glideTmpPath,
            'driver' => config('statamic.assets.image_manipulation.driver'),
            // 'cache_with_file_extensions' => true, // TODO: What is this doing?
        ]);

        try {
            $server->makeImage($file->getFilename(), $params);
        } catch (\Exception $exception) {
            return $file->getRealPath();
        }

        $newFilePath = collect($this->files->files($this->glideTmpPath.'/'.$file->getFilename()))
            ->first()
            ->getRealPath();

        return $newFilePath;
    }

    /**
     * Clear tmp glide cache.
     */
    private function glideClearTmpCache()
    {
        if ($this->files->exists($this->glideTmpPath)) {
            $this->files->deleteDirectory($this->glideTmpPath);
        }
    }
}
