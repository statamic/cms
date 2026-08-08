<?php

namespace Statamic\Http\Controllers\CP\ResourceIndexes;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\CP\ResourceIndex\ResourceIndexRepository;
use Statamic\Http\Controllers\CP\CpController;

class ResourceIndexOrganizationController extends CpController
{
    public function update(Request $request, string $resourceIndex, ResourceIndexRepository $repository)
    {
        $groups = $request->validate([
            'groups' => ['present', 'array'],
            'groups.*.id' => ['required', 'string', 'max:100', 'distinct', 'not_in:'.ResourceIndexRepository::FALLBACK_GROUP],
            'groups.*.title' => ['required', 'string', 'max:255'],
            'groups.*.items' => ['present', 'array'],
            'groups.*.items.*' => ['string', 'max:255'],
        ])['groups'];

        foreach ($groups as $groupIndex => $group) {
            if (count($group['items']) !== count(array_unique($group['items']))) {
                throw ValidationException::withMessages([
                    "groups.{$groupIndex}.items" => __('An item may only appear once within a group.'),
                ]);
            }
        }

        $repository->saveGroups($resourceIndex, $groups);

        return response('', 204);
    }

    public function destroy(string $resourceIndex, ResourceIndexRepository $repository)
    {
        $repository->resetGroups($resourceIndex);

        return response('', 204);
    }
}
