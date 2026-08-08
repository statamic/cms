<?php

namespace Statamic\CP\ResourceIndex;

use Closure;
use Inertia\Inertia;
use Statamic\Contracts\CP\ResourceIndex\GroupRepository;
use Statamic\Facades\User;

class ResourceIndexRepository
{
    public const FALLBACK_GROUP = '__other';

    public function __construct(private GroupRepository $groupRepository)
    {
    }

    public function make(string $handle, iterable $items = []): ResourceIndex
    {
        return new ResourceIndex($handle, $items);
    }

    public function groups(ResourceIndex $index): array
    {
        $defaults = $this->normalizeGroups($index->resolveDefaultGroups());
        $savedGroups = $this->groupRepository->find($index->handle());
        $groups = $defaults;

        if ($savedGroups !== null) {
            $groups = $this->normalizeGroups($savedGroups);
        }

        $defaultTitles = collect($defaults)->pluck('title', 'id');

        return collect($groups)
            ->map(function ($group) use ($defaultTitles) {
                if ($group['title'] === null || $group['title'] === '') {
                    $group['title'] = $defaultTitles->get($group['id'], $group['id']);
                }

                return $group;
            })
            ->values()
            ->all();
    }

    public function hasSavedGroups(ResourceIndex|string $index): bool
    {
        return $this->groupRepository->find($this->handle($index)) !== null;
    }

    public function saveGroups(ResourceIndex|string $index, array $groups): void
    {
        $this->groupRepository->save($this->handle($index), $this->normalizeGroups($groups));
    }

    public function resetGroups(ResourceIndex|string $index): void
    {
        $this->groupRepository->delete($this->handle($index));
    }

    public function pageProps(ResourceIndex $index): array
    {
        $organizeUrl = null;

        if ($this->canOrganize()) {
            $organizeUrl = request()->fullUrlWithQuery(['resource-index' => 'organize']);
        }

        return [
            'handle' => $index->handle(),
            'title' => $index->title(),
            'itemLabel' => $index->itemLabel(),
            'icon' => $index->icon(),
            'groups' => $this->groups($index),
            'hasSavedGroups' => $this->hasSavedGroups($index),
            'fallbackGroup' => [
                'id' => self::FALLBACK_GROUP,
                'title' => $index->fallbackLabel(),
            ],
            'organizeUrl' => $organizeUrl,
        ];
    }

    public function render(ResourceIndex $index, Closure $response)
    {
        if (request()->query('resource-index') === 'organize') {
            abort_unless($this->canOrganize(), 403);

            return $this->renderOrganizer($index);
        }

        return $response()->with([
            'resourceIndex' => $this->pageProps($index),
        ]);
    }

    public function canOrganize(): bool
    {
        return User::current()?->can('manage preferences');
    }

    protected function renderOrganizer(ResourceIndex $index)
    {
        $items = $index->all();

        return Inertia::render('resource-indexes/Organize', [
            'resourceIndex' => [
                'handle' => $index->handle(),
                'title' => $index->title(),
                'itemLabel' => $index->itemLabel(),
                'icon' => $index->icon(),
                'fallbackGroup' => [
                    'id' => self::FALLBACK_GROUP,
                    'title' => $index->fallbackLabel(),
                ],
            ],
            'items' => $items->map(fn ($item) => [
                'id' => (string) $item['id'],
                'title' => $item['organizer_title'] ?? $item['title'],
                'icon' => $item['icon'] ?? $index->icon(),
            ])->values(),
            'groups' => $this->groups($index),
            'hasSavedGroups' => $this->hasSavedGroups($index),
            'updateUrl' => cp_route('resource-indexes.organization.update', $index->handle()),
            'resetUrl' => cp_route('resource-indexes.organization.destroy', $index->handle()),
        ]);
    }

    protected function normalizeGroups(array $groups): array
    {
        return collect($groups)
            ->filter(fn ($group) => $this->shouldNormalizeGroup($group))
            ->map(function ($group) {
                $title = null;

                if (isset($group['title'])) {
                    $title = (string) $group['title'];
                }

                return [
                    'id' => (string) $group['id'],
                    'title' => $title,
                    'items' => collect($group['items'] ?? [])->map(fn ($id) => (string) $id)->unique()->values()->all(),
                ];
            })
            ->unique('id')
            ->values()
            ->all();
    }

    protected function shouldNormalizeGroup(mixed $group): bool
    {
        if (! is_array($group)) {
            return false;
        }

        if (! isset($group['id'])) {
            return false;
        }

        return $group['id'] !== self::FALLBACK_GROUP;
    }

    protected function handle(ResourceIndex|string $index): string
    {
        if ($index instanceof ResourceIndex) {
            return $index->handle();
        }

        return $index;
    }
}
