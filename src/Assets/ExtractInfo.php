<?php

namespace Statamic\Assets;

use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Statamic\Contracts\Assets\Asset;

class ExtractInfo
{
    public function fromAsset(Asset $asset): array
    {
        $path = $asset->path();
        $disk = $asset->disk()->filesystem();
        $size = $disk->size($path);
        $stream = $disk instanceof AwsS3V3Adapter
            ? StreamWrapper::getResource(new S3MetadataStream($disk, $path, $size))
            : $disk->readStream($path);

        try {
            $id3 = new \getID3;

            if ($disk instanceof AwsS3V3Adapter) {
                // getID3 reads up to a quarter of this allowance per MP4 atom.
                $id3->memory_limit = min($id3->memory_limit, 128 * 1024 * 1024);
            }

            return $id3->analyze($path, $size, '', $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }
}
