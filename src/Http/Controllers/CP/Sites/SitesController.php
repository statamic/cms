<?php

namespace Statamic\Http\Controllers\CP\Sites;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\CP\CpController;

use function Statamic\trans as __;

class SitesController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure sites');
    }

    public function edit()
    {
        $blueprint = Site::blueprint();

        $fields = $blueprint
            ->fields()
            ->addValues($this->values())
            ->preProcess();

        return Inertia::render('sites/Edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'initialValues' => $fields->values(),
            'meta' => $fields->meta(),
            'updateUrl' => cp_route('sites.update'),
        ]);
    }

    private function values(): array
    {
        return Site::blueprintValues();
    }

    public function update(Request $request)
    {
        $payload = Site::normalizeBlueprintValues($request->all());

        $blueprint = Site::blueprint($payload);

        $fields = $blueprint
            ->fields()
            ->addValues($payload);

        $fields->validate($this->uniqueHandleRules($payload));

        $values = $this->valuesInRequestOrder(
            $payload,
            $fields->process()->values()->all()
        );

        $sites = Site::configFromBlueprintValues($values);

        if (Site::multiEnabled() && empty($sites)) {
            throw ValidationException::withMessages(
                $this->emptySitesError($payload)
            );
        }

        Site::setSites($sites)->save();

        return response('', 204);
    }

    private function valuesInRequestOrder(array $request, array $values): array
    {
        return collect(array_keys($request))
            ->filter(fn ($key) => array_key_exists($key, $values) || $this->isGroupNameKey($key))
            ->mapWithKeys(fn ($key) => [$key => array_key_exists($key, $values) ? $values[$key] : $request[$key]])
            ->union($values)
            ->all();
    }

    private function isGroupNameKey(string $key): bool
    {
        return (bool) preg_match('/^group_[A-Za-z0-9_-]+_name$/', $key);
    }

    private function emptySitesError(array $request): array
    {
        $keys = collect($request)
            ->keys()
            ->filter(fn ($key) => is_string($key) && preg_match('/^group_[A-Za-z0-9_-]+_sites$/', $key));

        if ($keys->isEmpty()) {
            $keys = collect(['group_other_sites']);
        }

        return $keys
            ->mapWithKeys(fn ($key) => [$key => [__('statamic::validation.required')]])
            ->all();
    }

    private function uniqueHandleRules(array $values): array
    {
        $handles = collect();

        foreach ($values as $key => $sites) {
            if (! is_array($sites) || ! preg_match('/^group_[A-Za-z0-9_-]+_sites$/', $key)) {
                continue;
            }

            foreach ($sites as $index => $site) {
                $handle = $site['handle'] ?? null;

                if (! is_string($handle) || $handle === '') {
                    continue;
                }

                $handles["{$key}.{$index}.handle"] = $handle;
            }
        }

        $duplicates = $handles->countBy()->filter(fn ($count) => $count > 1)->keys();

        return $handles
            ->filter(fn ($handle) => $duplicates->contains($handle))
            ->map(fn () => [
                fn ($attribute, $value, $fail) => $fail(__('statamic::validation.unique')),
            ])
            ->all();
    }
}
