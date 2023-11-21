<?php

namespace Statamic\Assets;

use Illuminate\Support\Carbon;
use Statamic\Facades\Path;
use Statamic\Support\Str;
use Stringy\Stringy;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetUploader extends Uploader
{
    private $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public static function asset(Asset $asset)
    {
        return new static($asset);
    }

    protected function uploadPath(UploadedFile $file)
    {
        $ext = $this->getFileExtension($file);
        $filename = self::getSafeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

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

    protected function preset()
    {
        return $this->asset->container()->sourcePreset();
    }

    protected function disk()
    {
        return $this->asset->container()->disk();
    }

    private function getFileExtension(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $guessed = $file->guessExtension();

        // Only use the guessed extension if it's different than the original.
        // This allows us to maintain the casing of the original extension
        // if the the "lowercase filenames" config option is disabled.
        return strtolower($extension) === strtolower($guessed) ? $extension : $guessed;
    }

    public static function getSafeFilename($string)
    {
        $replacements = [
            ' ' => '-',
            '#' => '-',
        ];

        $str = Stringy::create(urldecode($string))->toAscii();

        foreach ($replacements as $from => $to) {
            $str = $str->replace($from, $to);
        }

        if (config('statamic.assets.lowercase')) {
            $str = strtolower($str);
        }

        return (string) $str;
    }
}
