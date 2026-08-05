<?php

namespace Statamic\Http\Controllers\CP\Forms\Connections;

use Illuminate\Http\Request;
use Statamic\Forms\Connections\ConnectionLogic;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class WebhookConnectionController extends CpController
{
    public function update(Request $request, $form)
    {
        $request->validate([
            'webhooks' => ['present', 'array'],
            'webhooks.*' => ['array'],
            'webhooks.*.url' => ['required', 'url:http,https'],
            'webhooks.*.verify_ssl' => ['sometimes', 'boolean'],
        ]);

        $webhooks = collect($request->webhooks)
            ->map(fn (array $config) => Arr::removeNullValues([
                'id' => Arr::get($config, 'id') ?? Str::random(8),
                ...Arr::except($config, ['_id', 'id']),
                'enabled' => Arr::get($config, 'enabled') === false ? false : null,
                'verify_ssl' => Arr::get($config, 'verify_ssl') === false ? false : null,
                'conditions' => ConnectionLogic::normalize(Arr::get($config, 'conditions') ?? []),
            ]))
            ->values()
            ->all();

        $form->connections($form->connections()->put('webhook', $webhooks))->save();
    }
}
