<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Asset;
use Statamic\CP\Publish\ProcessesFields;

class AssetsController extends CpController
{
    use ProcessesFields;

    public function index()
    {
        return redirect()->cpRoute('assets.browse.index');
    }

    public function show($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        $asset = $this->supplementAssetForEditing($asset);

        $fields = $this->addBlankFields($asset->fieldset(), $asset->processedData());

        return ['asset' => $asset->toArray(), 'fields' => $fields];
    }

    private function supplementAssetForEditing($asset)
    {
        if ($asset->isImage()) {
            $asset->setSupplement('width', $asset->width());
            $asset->setSupplement('height', $asset->height());

            // Public asset containers can use their regular URLs.
            // Private ones don't have URLs so we'll generate an actual-size "thumbnail".
            $asset->setSupplement('preview', $asset->container()->url() ? $asset->absoluteUrl() : $this->thumbnail($asset));
        }

        $asset->setSupplement('last_modified_relative', $asset->lastModified()->diffForHumans());
        $asset->setSupplement('download_url', cp_route('assets.download', base64_encode($asset->id())));

        return $asset;
    }

    public function download($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        $file = $asset->path();

        $filesystem = $asset->disk()->filesystem()->getDriver();
        $stream = $filesystem->readStream($file);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            "Content-Type" => $filesystem->getMimetype($file),
            "Content-Length" => $filesystem->getSize($file),
            "Content-disposition" => "attachment; filename=\"" . basename($file) . "\"",
        ]);
    }
}
