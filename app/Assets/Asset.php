<?php

namespace Statamic\Assets;

use Carbon\Carbon;
use Stringy\Stringy;
use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Site;
use Statamic\API\Event;
use Statamic\API\Image;
use Statamic\Data\Data;
use Statamic\API\Blueprint;
use Statamic\Data\ContainsData;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Facades\Statamic\Assets\Dimensions;
use Statamic\Events\Data\AssetReplaced;
use Statamic\Events\Data\AssetUploaded;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\API\AssetContainer as AssetContainerAPI;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;

class Asset implements AssetContract, Arrayable
{
    use ContainsData;

    protected $container;
    protected $path;

    public function id()
    {
        return $this->container->id() . '::' . $this->path();
    }

    /**
     * Get the container's filesystem disk instance
     *
     * @return \Statamic\Filesystem\FlysystemAdapter
     */
    public function disk()
    {
        return $this->container()->disk();
    }

    /**
     * Get the filename of the asset
     *
     * Eg. For a path of foo/bar/baz.jpg, the filename will be "baz"
     *
     * @return string
     */
    public function filename()
    {
        return pathinfo($this->path())['filename'];
    }

    /**
     * Get the basename of the asset
     *
     * Eg. for a path of foo/bar/baz.jpg, the basename will be "baz.jpg"
     *
     * @return string
     */
    public function basename()
    {
        return pathinfo($this->path())['basename'];
    }

    /**
     * Get the folder (or directory) of the asset
     *
     * Eg. for a path of foo/bar/baz.jpg, the folder will be "foo/bar"
     *
     * @return mixed
     */
    public function folder()
    {
        return pathinfo($this->path())['dirname'];
    }

    /**
     * Get or set the path to the data
     *
     * @param string|null $path Path to set
     * @return mixed
     */
    public function path($path = null)
    {
        if (func_num_args() === 0) {
            return $this->path;
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Get the resolved path to the asset
     *
     * This is the "actual" path to the asset.
     * It combines the container path with the asset path.
     *
     * @return string
     */
    public function resolvedPath()
    {
        return Path::tidy($this->container()->diskPath() . '/' . $this->path());
    }

    /**
     * Get the asset's URL
     *
     * @return string
     */
    public function url()
    {
        if ($this->container()->private()) {
            return null;
        }

        return URL::assemble($this->container()->url(), $this->path());
    }

    public function value()
    {
        $url = $this->url();
        $siteUrl = Site::current()->absoluteUrl();

        if (starts_with($url, $siteUrl)) {
            return '/'.str_after($url, $siteUrl);
        }

        return $url;
    }

    /**
     * Get either a image URL builder instance, or a URL if passed params.
     *
     * @param null|array $params Optional manipulation parameters to return a string right away
     * @return \Statamic\Contracts\Imaging\UrlBuilder|string
     * @throws \Exception
     */
    public function manipulate($params = null)
    {
        return Image::manipulate($this, $params);
    }

    /**
     * Is this asset an audio file?
     *
     * @return bool
     */
    public function isAudio()
    {
        return $this->extensionIsOneOf(['aac', 'flac', 'm4a', 'mp3', 'ogg', 'wav']);
    }

    /**
     * Is this asset a Google Docs previewable file?
     * https://gist.github.com/izazueta/4961650
     *
     * @return bool
     */
    public function isPreviewable()
    {
        return $this->extensionIsOneOf([
            'doc', 'docx', 'pages', 'txt',
            'ai', 'psd', 'eps', 'ps',
            'css', 'html', 'php', 'c', 'cpp', 'h', 'hpp', 'js',
            'ppt', 'pptx',
            'flv',
            'tiff',
            'ttf',
            'dxf', 'xps',
            'zip', 'rar',
            'xls', 'xlsx'
        ]);
    }

    /**
     * Is this asset an image?
     *
     * @return bool
     */
    public function isImage()
    {
        return $this->extensionIsOneOf(['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * Is this asset a video file?
     *
     * @return bool
     */
    public function isVideo()
    {
        return $this->extensionIsOneOf(['h264', 'mp4', 'm4v', 'ogv', 'webm']);
    }

    /**
     * Get the file extension of the asset
     *
     * @return string
     */
    public function extension()
    {
        return Path::extension($this->path());
    }

    /**
     * Get the last modified time of the asset
     *
     * @return \Carbon\Carbon
     */
    public function lastModified()
    {
        return Carbon::createFromTimestamp($this->disk()->lastModified($this->path()));
    }

    /**
     * Save the asset
     *
     * @return void
     */
    public function save()
    {
        $this->container()->addAsset($this)->save();

        event('asset.saved', $this);

        return $this;
    }

    /**
     * Delete the asset
     *
     * @return mixed
     */
    public function delete()
    {
        // Delete the data from the container, if any is in there.
        $this->container()->removeAsset($this)->save();

        // Also, delete the actual file
        $this->disk()->delete($this->path());

        return $this;
    }

    /**
     * Get or set the container where this asset is located
     *
     * @param string|AssetContainerContract $container  ID of the container
     * @return AssetContainerContract
     */
    public function container($container = null)
    {
        if (func_num_args() === 0) {
            return $this->container;
        }

        if (is_string($container)) {
            $container = AssetContainerAPI::find($container);
        }

        $this->container = $container;

        return $this;
    }

    /**
     * Get the container's ID
     *
     * @return string
     */
    public function containerId()
    {
        return $this->container->id();
    }

    /**
     * Rename the asset
     *
     * @param string $filename
     * @return void
     */
    public function rename($filename)
    {
        return $this->move($this->folder(), $filename);
    }

    /**
     * Move the asset to a different location
     *
     * @param string      $folder   The folder relative to the container.
     * @param string|null $filename The new filename, if renaming.
     * @return void
     */
    public function move($folder, $filename = null)
    {
        $filename = $filename ?: $this->filename();

        // Remove the asset definition from the container.
        // It'll be re-added in a moment under its new path.
        $this->container()->removeAsset($this);

        $oldPath = $this->path();

        $newPath = $folder . '/' . $filename . '.' . pathinfo($oldPath, PATHINFO_EXTENSION);

        // Actually rename the file.
        $this->disk()->rename($oldPath, $newPath);

        $this
            ->path($newPath)
            ->save();

        return $this;
    }

    /**
     * Get the asset's dimensions
     *
     * @return array  An array in the [width, height] format
     */
    public function dimensions()
    {
        return Dimensions::asset($this)->get();
    }

    /**
     * Get the asset's width
     *
     * @return int|null
     */
    public function width()
    {
        return $this->dimensions()[0];
    }

    /**
     * Get the asset's height
     *
     * @return int|null
     */
    public function height()
    {
        return $this->dimensions()[1];
    }

    /**
     * Get the asset's file size
     *
     * @return int
     */
    public function size()
    {
        return $this->disk()->size($this->path());
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = [
            'id'             => $this->id(),
            'title'          => $this->get('title'),
            'path'           => $this->path(),
            'filename'       => $this->filename(),
            'basename'       => $this->basename(),
            'extension'      => $this->extension(),
            'is_asset'       => true,
            'is_audio'       => $this->isAudio(),
            'is_previewable' => $this->isPreviewable(),
            'is_image'       => $this->isImage(),
            'is_video'       => $this->isVideo(),
            'blueprint'      => $this->blueprint()->handle(),
            'edit_url'       => $this->editUrl(),
            'container'      => $this->container()->id(),
            'folder'         => $this->folder(),
            // 'value'          => $this->value(), // TODO: test this
        ];

        if ($this->disk() && $this->disk()->exists($this->path())) {
            $size = $this->size();
            $kb = number_format($size / 1024, 2);
            $mb = number_format($size / 1048576, 2);
            $gb = number_format($size / 1073741824, 2);

            $attributes = array_merge($attributes, [
                'size'           => $this->disk()->sizeHuman($this->path()),
                'size_bytes'     => $size,
                'size_kilobytes' => $kb,
                'size_megabytes' => $mb,
                'size_gigabytes' => $gb,
                'size_b'         => $size,
                'size_kb'        => $kb,
                'size_mb'        => $mb,
                'size_gb'        => $gb,
                'last_modified'  => (string) $this->lastModified(),
                'last_modified_timestamp' => $this->lastModified()->timestamp,
                'last_modified_instance'  => $this->lastModified(),
                'focus_css' => \Statamic\View\Modify::value($this->get('focus'))->backgroundPosition()->fetch(),
            ]);
        }

        return array_merge($this->data(), $attributes, $this->supplements);
    }

    /**
     * Upload a file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return void
     */
    public function upload(UploadedFile $file)
    {
        $ext       = $file->getClientOriginalExtension();
        $filename  = $this->getSafeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $basename  = $filename . '.' . $ext;

        $directory = $this->folder();
        $directory = ($directory === '.') ? '/' : $directory;
        $path      = Path::tidy($directory . '/' . $filename . '.' . $ext);

        // If the file exists, we'll append a timestamp to prevent overwriting.
        if ($this->disk()->exists($path)) {
            $basename = $filename . '-' . time() . '.' . $ext;
            $path = Str::removeLeft(Path::assemble($directory, $basename), '/');
        }

        $this->performUpload($file, $path);

        $this->path($path);

        event(new AssetUploaded($this));

        // Legacy/Deprecated. TODO: Remove in 2.3
        event('asset.uploaded', $path);
    }

    private function getSafeFilename($string)
    {
        $replacements = [
            ' ' => '-',
            '#' => '-',
        ];

        $str = Stringy::create($string)->toAscii();

        foreach ($replacements as $from => $to) {
            $str = $str->replace($from, $to);
        }

        return (string) $str;
    }

    /**
     * Actually perform the file upload.
     *
     * Saves the file to a temporary location on the local filesystem, then moves it to the
     * right place. This is a workaround for needing to know the file extension or mime
     * type when uploading to Amazon S3. Temporary files don't have file extensions
     * so sending directly to S3 causes it to appear with the wrong mime type.
     *
     * @param UploadedFile $file
     * @param string $path
     * @return void
     */
    private function performUpload(UploadedFile $file, $path)
    {
        // Build up a path where the file will be temporarily stored
        $temp = 'uploads/'.md5($file->getRealPath().microtime(true)).'.'.$file->getClientOriginalExtension();

        // Upload to a temporary location
        $temp_disk = new Filesystem(new Local(temp_path()));
        $stream = fopen($file->getRealPath(), 'r+');
        $temp_disk->putStream($temp, $stream);
        fclose($stream);

        // Move from the temporary location to the real container location
        $this->disk()->put($path, $temp_disk->readStream($temp));

        // Delete the temporary file
        $temp_disk->delete($temp);
    }

    /**
     * Replace the file
     *
     * @param string|resource $contents  Either raw contents of a file, or a resource stream
     */
    public function replace($contents)
    {
        $this->disk()->put($this->path(), $contents);

        event(new AssetReplaced($this));
    }

    /**
     * Get the blueprint
     *
     * @param string|null $blueprint
     * @return \Statamic\Fields\Blueprint
     */
    public function blueprint()
    {
        return $this->container()->blueprint();
    }

    /**
     * The URL to edit it in the CP
     *
     * @return mixed
     */
    public function editUrl()
    {
        return cp_route('assets.edit', base64_encode($this->id()));
    }

    /**
     * Check if asset's file extension is one of a given list
     *
     * @return bool
     */
    public function extensionIsOneOf($filetypes = [])
    {
        return (in_array(strtolower($this->extension()), $filetypes));
    }
}
