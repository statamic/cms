<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;
use Statamic\Exceptions\AssetConflictException;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blink;
use Statamic\Facades\Glide;
use Statamic\Facades\Path;
use Statamic\Facades\User;
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

                if ($strategy === 'overwrite') {
                    if ($existingAsset && ! User::current()->can('delete', $existingAsset)) {
                        throw new \Exception(__('You are not authorized to delete this asset.'));
                    }

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

                $messageKey = $sourceLastModified >= $destinationLastModified
                    ? 'statamic::messages.asset_conflict_message_newer_replaces_older'
                    : 'statamic::messages.asset_conflict_message_older_replaces_newer';

                throw new AssetConflictException(
                    __($messageKey, [
                        'filename' => $asset->basename(),
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
