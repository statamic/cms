<?php

namespace Statamic\Assets;

use Illuminate\Support\Carbon;
use Statamic\Facades\Image;
use Statamic\Facades\Path;
use Statamic\Support\Arr;
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
        $ext = $this->getNewExtension() ?? $file->getClientOriginalExtension();

        if (config('statamic.assets.lowercase')) {
            $ext = strtolower($ext);
        }

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

    protected function getNewExtension()
    {
        if (! $preset = $this->asset->container()->sourcePreset()) {
            return null;
        }

        if (! $ext = Arr::get(Image::userManipulationPresets(), "$preset.fm")) {
            return null;
        }

        return $ext;
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
