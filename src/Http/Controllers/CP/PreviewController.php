<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\CP\LivePreview;
use Illuminate\Http\Request;
use Statamic\Facades\URL;
use Statamic\Support\Arr;
use Statamic\Support\Str;

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

        foreach (Arr::except($fields->values()->all(), ['slug']) as $key => $value) {
            $data->setSupplement($key, $value);
        }

        return $this->tokenizeAndReturn($request, $data);
    }

    protected function tokenizeAndReturn($request, $data)
    {
        $data->setSupplement('live_preview', empty($extras = $request->extras) ? true : $extras);

        return [
            'token' => $token = LivePreview::tokenize($request->token, $data)->token(),
            'url' => $this->getPreviewUrl($data, $request->target ?? 0, $token),
        ];
    }

    private function getPreviewUrl($data, $target, $token)
    {
        $url = URL::makeAbsolute($data->previewTargets()[$target]['url']);

        return vsprintf('%s%slive-preview=%s&token=%s', [
            $url,
            strpos($url, '?') === false ? '?' : '&',
            Str::random(), // random string to prevent caching
            $token,
        ]);
    }
}
