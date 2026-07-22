<?php

namespace Statamic\Http\Controllers\CP\Forms\Connections;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class WebhookConnectionController extends CpController
{
    public function update(Request $request, $form)
    {
        $request->validate([
            'configs' => ['present', 'array'],
            'configs.*' => ['array'],
            'configs.*.url' => ['required', 'url:http,https'],
            'configs.*.verify_ssl' => ['sometimes', 'boolean'],
        ]);

        $configs = collect($request->configs)
            ->map(fn ($config) => Arr::removeNullValues(array_merge(Arr::except($config, '_id'), [
                'enabled' => Arr::get($config, 'enabled') === false ? false : null,
                'verify_ssl' => Arr::get($config, 'verify_ssl') === false ? false : null,
                'conditions' => $this->normalizeConditions(Arr::get($config, 'conditions', [])),
            ])))
            ->values()
            ->all();

        $form->connections($form->connections()->put('webhook', $configs))->save();
    }

    private function normalizeConditions(array $conditions): ?array
    {
        $conditions = collect($conditions)
            ->map(fn ($condition) => Arr::only($condition, ['field', 'operator', 'value', 'join']))
            ->filter(fn ($condition) => Arr::get($condition, 'field') && filled(Arr::get($condition, 'value')))
            ->values();

        return $conditions->isNotEmpty() ? $conditions->all() : null;
    }
}
