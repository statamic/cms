<?php

namespace Statamic\Http\Controllers\CP\Forms\Connections;

use Illuminate\Http\Request;
use Statamic\Forms\Connections\ConnectionLogic;
use Statamic\Forms\Connections\Emails;
use Statamic\Forms\Connections\Rules\EmailConnectionAddress;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class EmailConnectionController extends CpController
{
    public function update(Request $request, $form)
    {
        $request->validate([
            'configs' => ['present', 'array'],
            'configs.*' => ['array'],
            'configs.*.to' => ['required', new EmailConnectionAddress($form)],
            'configs.*.cc' => [new EmailConnectionAddress($form)],
            'configs.*.bcc' => [new EmailConnectionAddress($form)],
            'configs.*.from' => [new EmailConnectionAddress($form)],
            'configs.*.reply_to' => [new EmailConnectionAddress($form)],
        ]);

        $emailConfigs = collect($request->configs)
            ->map(function (array $config) use ($form): array {
                $config = Arr::removeNullValues($config);

                $values = Emails::blueprint($form)->fields()
                    ->addValues(Arr::except($config, ['_id', 'id', 'enabled', 'conditions']))
                    ->process()
                    ->values()
                    ->all();

                return Arr::removeNullValues([
                    'id' => Arr::get($config, 'id'),
                    ...$values,
                    'enabled' => Arr::get($config, 'enabled') === false ? false : null,
                    'markdown' => Arr::get($values, 'markdown') === true ? true : null,
                    'attachments' => Arr::get($values, 'attachments') === true ? true : null,
                    'conditions' => ConnectionLogic::normalize(Arr::get($config, 'conditions') ?? []),
                ]);
            })
            ->values()
            ->all();

        $form->connections($form->connections()->put('email', $emailConfigs))->save();
    }
}
