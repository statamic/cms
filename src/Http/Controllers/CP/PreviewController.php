<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\CP\LivePreview;
use Illuminate\Http\Request;

class PreviewController extends CpController
{
    public function show()
    {
        return view('statamic::entries.preview');
    }

    public function edit(Request $request, $_, $data)
    {
        $this->authorize('view', $data);

        $fields = $data->blueprint()
            ->fields()
            ->addValues($request->input('preview', []))
            ->process();

        foreach (array_except($fields->values()->all(), ['slug']) as $key => $value) {
            $data->setSupplement($key, $value);
        }

        return $this->tokenizeAndReturn($request, $data);
    }

    protected function tokenizeAndReturn($request, $data)
    {
        return [
            'token' => $token = LivePreview::tokenize($request->token, $data)->token(),
            'url' => $this->getPreviewUrl($data, $request->target, $token),
        ];
    }

    private function getPreviewUrl($data, $target, $token)
    {
        $url = $data->previewTargets()[$target]['url'];

        return $url.(strpos($url, '?') === false ? '?' : '&').'token='.$token;
    }
}
