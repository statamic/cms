<?php

namespace Statamic\CP\Breadcrumbs;

use Statamic\CP\Navigation\NavItem;
use Statamic\Facades\CP\Nav;
use Statamic\Statamic;

class Breadcrumbs
{
    public static $pushed = [];

    public static function push(Breadcrumb $breadcrumb)
    {
        static::$pushed[] = $breadcrumb;
    }

    public static function build(): array
    {
        $breadcrumbs = Nav::build(preferences: false)->map(function (array $section): ?array {
            $primaryNavItem = $section['items']->first(function (NavItem $navItem) {
                return $navItem->isActive();
            });

            if (! $primaryNavItem) {
                return null;
            }

            if ($primaryNavItem->resolveChildren()->children()?->isNotEmpty()) {
                $secondaryNavItem = $primaryNavItem->children()->first(function (NavItem $navItem) {
                    return $navItem->isActive();
                });
            } else {
                $secondaryNavItem = null;
            }

            return array_filter([
                new Breadcrumb(
                    text: $primaryNavItem->display(),
                    url: $primaryNavItem->url(),
                    icon: $primaryNavItem->icon(),
                    links: $section['items']
                        ->reject(fn (NavItem $navItem) => $navItem === $primaryNavItem)
                        ->map(fn (NavItem $navItem) => [
                            'icon' => $navItem->icon(),
                            'text' => $navItem->display(),
                            'url' => $navItem->url(),
                        ])
                        ->values()
                        ->all(),
                ),

                $secondaryNavItem ? new Breadcrumb(
                    text: $secondaryNavItem->display(),
                    url: $secondaryNavItem->url(),
                    icon: $secondaryNavItem->icon(),
                    links: $primaryNavItem->children()
                        ->reject(fn (NavItem $navItem) => $navItem === $secondaryNavItem)
                        ->map(fn (NavItem $navItem) => [
                            'icon' => $navItem->icon(),
                            'text' => $navItem->display(),
                            'url' => $navItem->url(),
                        ])
                        ->values()
                        ->all(),
                    createLabel: $primaryNavItem->extra()['breadcrumbs']['create_label'] ?? null,
                    createUrl: $primaryNavItem->extra()['breadcrumbs']['create_url'] ?? null,
                    configureUrl: $secondaryNavItem->extra()['breadcrumbs']['configure_url'] ?? null,
                ) : null,
            ]);
        })->filter()->first() ?? [];

        return [
            ...$breadcrumbs,
            ...static::$pushed,
        ];
    }

    public static function title(?string $title = null): string
    {
        $crumbs = collect(static::build())->map->text();

        if ($title) {
            $crumbs->push(__($title));
        }

        $arrow = Statamic::cpDirection() === 'ltr' ? ' ‹ ' : ' › ';

        return $crumbs->reverse()->join($arrow);
    }
}
