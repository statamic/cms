<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Asset;
use Illuminate\Http\Request;
use Statamic\Fields\Validation;
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

        $fields = $asset->blueprint()->fields()
            ->addValues($asset->data())
            ->preProcess()
            ->values();

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

    public function update(Request $request, $asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        $fields = $asset->blueprint()->fields()->addValues($request->all())->process();

        $request->validate((new Validation)->fields($fields)->rules());

        foreach ($fields->values() as $key => $value) {
            $asset->set($key, $value);
        }

        $asset->save();

        if ($asset->isImage()) {
            $asset->set('thumbnail', $this->thumbnail($asset, 'small'));
            $asset->set('toenail', $this->thumbnail($asset, 'large'));
        }

        return ['success' => true, 'message' => 'Asset updated', 'asset' => $asset->toArray()];
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

    public function destroy($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        $asset->delete();

        return response('', 204);
    }

    private function thumbnail($asset, $preset = null)
    {
        return cp_route('assets.thumbnails.show', [
            'asset' => base64_encode($asset->id()),
            'size' => $preset
        ]);
    }
}
