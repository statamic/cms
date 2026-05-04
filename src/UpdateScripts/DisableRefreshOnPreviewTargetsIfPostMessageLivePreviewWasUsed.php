<?php

namespace Statamic\UpdateScripts;

use Statamic\Entries\Collection;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Taxonomy as TaxonomyFacade;
use Statamic\Taxonomies\Taxonomy;

class DisableRefreshOnPreviewTargetsIfPostMessageLivePreviewWasUsed extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.4.8');
    }

    public function update()
    {
        $this->console->warn('3.4.8 uses an updated live preview. A few changes are required to upgrade.');

        $usedLivePreviewUsingPostMessage = $this->console->confirm("Have you previously used the live preview using postMessage? (Is the 'post_message_data' config set to something other than null?)");

        $this->console->confirm("Remove the 'post_message_data' config from config/statamic/live_preview.php entirely. Done?");

        if (! $usedLivePreviewUsingPostMessage) {
            return;
        }

        $this->console->info("We'll update your collections' and your taxonomies' preview targets accordingly. Set 'refresh' in the preview targets to true if instead you want to use the default live preview behaviour.");

        CollectionFacade::all()->each(function (Collection $collection) {
            $previewTargets = $collection->previewTargets();

            $collection->previewTargets(
                $previewTargets->map(fn ($target) => array_merge($target, ['refresh' => false]))
            );

            $collection->save();
        });

        TaxonomyFacade::all()->each(function (Taxonomy $taxonomy) {
            $previewTargets = $taxonomy->previewTargets();

            $taxonomy->previewTargets(
                $previewTargets->map(fn ($target) => array_merge($target, ['refresh' => false]))
            );

            $taxonomy->save();
        });

        $this->console->confirm("If you've used the live preview using the nuxt plugin @teamnovu/statamic-live-preview-nuxt, you'll need to update it to version 2.x. Understood?");
    }
}
