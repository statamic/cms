<?php

namespace Statamic\Http\Controllers\CP\Forms\Connections;

use Illuminate\Http\Request;
use Statamic\Forms\Connections\CoreConnections;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class EmailConnectionController extends CpController
{
    public function update(Request $request, $form)
    {
        $request->validate([
            'configs' => ['present', 'array'],
            'configs.*' => ['array'],
            'configs.*.to' => ['required'],
        ]);

        $configs = collect($request->configs)
            ->map(fn ($config) => $this->normalize($config))
            ->values()
            ->all();

        $form->connections($form->connections()->put('email', $configs))->save();
    }

    private function normalize(array $config): array
    {
        $config = array_map(fn ($value) => $value === '' ? null : $value, $config);

        $values = CoreConnections::emailBlueprint()->fields()
            ->addValues(Arr::except($config, ['_id', 'id', 'enabled', 'conditions']))
            ->process()
            ->values()
            ->all();

        return Arr::removeNullValues(array_merge(['id' => Arr::get($config, 'id')], $values, [
            'enabled' => Arr::get($config, 'enabled') === false ? false : null,
            'markdown' => Arr::get($values, 'markdown') === true ? true : null,
            'attachments' => Arr::get($values, 'attachments') === true ? true : null,
            'conditions' => $this->normalizeConditions(Arr::get($config, 'conditions', [])),
        ]));
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
