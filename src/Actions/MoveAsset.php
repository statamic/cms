<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;
use Statamic\Exceptions\AssetConflictException;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blink;
use Statamic\Facades\Glide;
use Statamic\Facades\Path;
use Statamic\Support\Str;

use function Statamic\trans as __;

class MoveAsset extends Action
{
    protected $icon = 'move-folder';

    public static function title()
    {
        return __('Move');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function authorize($user, $asset)
    {
        return $user->can('move', $asset);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Move Asset|Move :count Assets';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to move this asset?|Are you sure you want to move these :count assets?';
    }

    public function run($assets, $values)
    {
        $folder = $values['folder'];
        $strategy = $this->context['conflict'] ?? 'cancel';
        $timestamp = now()->timestamp;
        $timestampCount = 0;
        $ids = [];
        $completedMoves = [];

        foreach ($assets as $index => $asset) {
            $destinationPath = Str::removeLeft(Path::tidy($folder.'/'.$asset->basename()), '/');
            $conflicts = $asset->path() !== $destinationPath && $asset->disk()->exists($destinationPath);

            if ($conflicts) {
                $existingAsset = $asset->container()->asset($destinationPath);
                $sourceLastModified = $asset->disk()->lastModified($asset->path());
                $destinationLastModified = $asset->disk()->lastModified($destinationPath);
                $movingAge = $sourceLastModified >= $destinationLastModified
                    ? __('statamic::messages.asset_conflict_newer')
                    : __('statamic::messages.asset_conflict_older');
                $existingDescriptor = $sourceLastModified >= $destinationLastModified
                    ? __('statamic::messages.asset_conflict_an_older')
                    : __('statamic::messages.asset_conflict_a_newer');

                if ($strategy === 'overwrite') {
                    if ($existingAsset) {
                        $existingAsset->delete();
                    } else {
                        Glide::clearAsset($asset->container()->makeAsset($destinationPath));
                        $asset->disk()->delete($destinationPath);
                    }

                    $oldId = $asset->id();
                    $newId = $asset->move($folder)->id();
                    $completedMoves[$oldId] = $newId;
                    $ids[] = $newId;

                    continue;
                }

                if ($strategy === 'timestamp') {
                    $filename = $asset->filename().'-'.$timestamp;

                    if ($timestampCount > 0) {
                        $filename .= '-'.$timestampCount;
                    }

                    $timestampCount++;
                    $oldId = $asset->id();
                    $newId = $asset->moveUnique($folder, $filename)->id();
                    $completedMoves[$oldId] = $newId;
                    $ids[] = $newId;

                    continue;
                }

                throw new AssetConflictException(
                    __('statamic::messages.asset_conflict_message', [
                        'filename' => $asset->basename(),
                        'existing_descriptor' => $existingDescriptor,
                        'moving_age' => $movingAge,
                    ]),
                    [
                        'conflict' => [
                            'type' => 'asset_move',
                            'asset' => [
                                'id' => $asset->id(),
                                'basename' => $asset->basename(),
                            ],
                            'existing' => [
                                'preview' => $existingAsset ? ($existingAsset->container()->accessible() ? $existingAsset->url() : $existingAsset->thumbnailUrl()) : null,
                                'thumbnail' => $existingAsset?->thumbnailUrl('small'),
                            ],
                            'destination' => $folder,
                        ],
                        'completed_moves' => (object) $completedMoves,
                    ],
                );
            }

            $oldId = $asset->id();
            $newId = $asset->move($folder)->id();
            $completedMoves[$oldId] = $newId;
            $ids[] = $newId;
        }

        return [
            'ids' => $ids,
            'callback' => ['replaceInSelections', $completedMoves],
        ];
    }

    protected function fieldItems()
    {
        $options = Blink::once('action-move-asset-folders', function () {
            return AssetContainer::find($this->context['container'])
                ->assetFolders()
                ->mapWithKeys(function ($folder) {
                    return [$folder->path() => $folder->path()];
                })
                ->prepend('/', '/')
                ->all();
        });

        return [
            'folder' => [
                'display' => __('Folder'),
                'type' => 'select',
                'options' => $options,
                'validate' => 'required',
            ],
        ];
    }
}
