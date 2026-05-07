<?php

namespace Statamic\Http\Controllers\CP\Updater;

use Facades\Statamic\Marketplace\Marketplace;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Facades\Addon;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Statamic;

class UpdateProductController extends CpController
{
    /**
     * Show product updates overview.
     *
     * @param  string  $slug
     */
    public function show($marketplaceProductSlug)
    {
        $this->authorize('view updates');

        if (! $product = Marketplace::product($marketplaceProductSlug)) {
            return $this->pageNotFound();
        }

        $packageLinks = collect()
            ->push([
                'text' => 'Statamic',
                'icon' => 'updates',
                'url' => cp_route('updater.product', Statamic::CORE_SLUG),
            ])
            ->merge(
                Addon::all()
                    ->filter->existsOnMarketplace()
                    ->reject(fn ($addon) => $addon->marketplaceSlug() === $marketplaceProductSlug)
                    ->map(fn ($addon) => [
                        'text' => $addon->name(),
                        'icon' => 'updates',
                        'url' => cp_route('updater.product', $addon->marketplaceSlug()),
                    ])
            )
            ->reject(fn ($link) => $link['url'] === request()->url())
            ->values()
            ->all();

        if (! empty($packageLinks)) {
            Breadcrumbs::push(new Breadcrumb(
                text: $product->name(),
                url: request()->url(),
                icon: 'updates',
                links: $packageLinks,
            ));
        }

        return Inertia::render('updater/Show', [
            'slug' => $marketplaceProductSlug,
            'package' => $product->package(),
            'name' => $product->name(),
        ]);
    }

    /**
     * Product changelog.
     *
     * @param  string  $slug
     */
    public function changelog(Request $request, $marketplaceProductSlug)
    {
        $this->authorize('view updates');

        if (! $product = Marketplace::product($marketplaceProductSlug)) {
            return $this->pageNotFound();
        }

        $changelog = $product->changelog();

        $paginated = $changelog->paginate(
            $request->input('page', 1),
            $request->input('perPage', 10)
        );

        return [
            'changelog' => $paginated['data'],
            'currentVersion' => $changelog->currentVersion(),
            'meta' => $paginated['meta'],
        ];
    }
}
