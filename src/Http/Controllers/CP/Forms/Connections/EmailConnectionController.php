<?php

namespace Statamic\Http\Controllers\CP\Forms\Connections;

use Illuminate\Http\Request;
use Statamic\Forms\Connections\ConnectionLogic;
use Statamic\Forms\Connections\Email;
use Statamic\Forms\Connections\Rules\EmailConnectionAddress;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class EmailConnectionController extends CpController
{
    public function update(Request $request, $form)
    {
        $request->validate([
            'emails' => ['present', 'array'],
            'emails.*' => ['array'],
            'emails.*.to' => ['required', new EmailConnectionAddress($form)],
            'emails.*.cc' => [new EmailConnectionAddress($form)],
            'emails.*.bcc' => [new EmailConnectionAddress($form)],
            'emails.*.from' => [new EmailConnectionAddress($form)],
            'emails.*.reply_to' => [new EmailConnectionAddress($form)],
        ]);

        $emails = collect($request->emails)
            ->map(function (array $config) use ($form): array {
                $config = Arr::removeNullValues($config);

                $values = Email::blueprint($form)->fields()
                    ->addValues(Arr::except($config, ['_id', 'id', 'enabled', 'conditions']))
                    ->process()
                    ->values()
                    ->all();

                return Arr::removeNullValues([
                    'id' => Arr::get($config, 'id') ?? Str::random(8),
                    ...$values,
                    'enabled' => Arr::get($config, 'enabled') === false ? false : null,
                    'markdown' => Arr::get($values, 'markdown') === true ? true : null,
                    'attachments' => Arr::get($values, 'attachments') === true ? true : null,
                    'conditions' => ConnectionLogic::normalize(Arr::get($config, 'conditions') ?? []),
                ]);
            })
            ->values()
            ->all();

        $form->connections($form->connections()->put('email', $emails))->save();
    }
}
