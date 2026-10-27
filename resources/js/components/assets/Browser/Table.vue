<template>
    <Card
        inset
        variant="flat"
        class="
            [&_tr:has([data-moving])]:pointer-events-none
            [&_tr:has([data-moved])]:pointer-events-none
            [&_tr:has([data-moved])]:opacity-0
            [&_tr:has([data-moved])]:transition-opacity
            [&_tr:has([data-moved])]:duration-300
            [&_tr:has([data-removed])]:hidden
        "
    >
        <ListingTable contained>
            <template #tbody-start>
                <tr
                    v-if="!restrictFolderNavigation"
                    v-for="(folder, i) in visibleFolders"
                    :key="folder.path"
                    class="pointer-events-auto"
                    :draggable="canMoveFolder(folder)"
                    @dragover="folderDragOver($event, folder)"
                    @dragleave="folderDragLeave"
                    @drop.prevent="handleFolderDrop(folder)"
                    @dragstart="draggingFolder = folder.path"
                    @dragend="
                        draggingFolder = null;
                        dragOverFolder = null;
                    "
                >
                    <td />
                    <td v-for="column in visibleColumns">
                        <template v-if="column.field === 'basename'">
                            <a
                                class="group -m-1 -me-2.5 flex w-fit cursor-pointer items-center rounded-lg p-1 pe-2.5"
                                :class="[
                                    moveStateClasses(folder.path),
                                    { 'ring-2 ring-blue-400': dragOverFolder === folder.path },
                                ]"
                                :data-moving="isMoving(folder.path) || null"
                                :data-moved="isMoved(folder.path) || null"
                                @click="selectFolder(folder.path)"
                            >
                                <div class="relative me-2 flex size-8 shrink-0 items-center justify-center">
                                    <AnimatedFolder
                                        :drop="dropFor(folder)"
                                        class="w-6.5 text-blue-400/90 group-hover:text-blue-400"
                                    />
                                    <div
                                        v-if="isMoving(folder.path)"
                                        class="absolute inset-0 flex animate-in fade-in fill-mode-both items-center justify-center [animation-delay:400ms]"
                                    >
                                        <ui-icon name="loading" class="size-5 text-gray-700 dark:text-gray-200" />
                                    </div>
                                </div>
                                {{ folder.basename }}
                            </a>
                        </template>
                    </td>
                    <td class="actions-column pe-3!">
                        <ItemActions
                            :url="folderActionUrl"
                            :actions="folder.actions"
                            :item="folder.path"
                            @started="actionStarted"
                            @completed="actionCompleted"
                            v-slot="{ actions }"
                        >
                            <Dropdown placement="left-start" v-if="folderActions(folder).length">
                                <DropdownMenu>
                                    <DropdownItem
                                        v-for="action in actions"
                                        :key="action.handle"
                                        :text="__(action.title)"
                                        :icon="action.icon"
                                        :variant="action.dangerous ? 'destructive' : 'default'"
                                        @click="action.run"
                                    />
                                </DropdownMenu>
                            </Dropdown>
                        </ItemActions>
                    </td>
                </tr>
                <tr v-if="creatingFolder">
                    <td />
                    <td :colspan="visibleColumns.length + 1">
                        <a class="group flex cursor-pointer items-center">
                            <file-icon
                                extension="folder"
                                class="me-2 inline-block size-8 shrink-0 text-blue-400/90 group-hover:text-blue-400
                                dark:text-blue-400/90 dark:group-hover:text-blue-400"
                            />
                            <Editable
                                ref="newFolderInput"
                                v-model:modelValue="newFolderName"
                                :start-with-edit-mode="true"
                                submit-mode="both"
                                :placeholder="__('Name')"
                                :class="[
                                    'placeholder:lowercase',
                                    { 'st-has-error': creatingFolderError }
                                ]"
                                @submit="$emit('create-folder', newFolderName)"
                                @cancel="
                                    () => {
                                        newFolderName = null;
                                        $emit('cancel-creating-folder');
                                    }
                                "
                            />
                        </a>
                    </td>
                </tr>
            </template>

            <template #cell-basename="{ row: asset, checkboxId }">
                <div
                    class="group flex w-fit items-center gap-2 sm:gap-3"
                    :class="[
                        moveStateClasses(asset.id),
                        { 'text-gray-400 dark:text-gray-500': isDragging(asset.id) },
                    ]"
                    :draggable="true"
                    @dragover.prevent
                    @dragstart="assetDragStarted($event, asset)"
                    @dragend="draggingAsset = null"
                    :data-moving="isMoving(asset.id) || null"
                    :data-moved="isMoved(asset.id) || null"
                    :data-removed="isRemoved(asset.id) || null"
                >
                    <div class="relative shrink-0">
                        <asset-thumbnail
                            :asset="asset"
                            alt=""
                            :square="true"
                            class="size-8 cursor-pointer"
                            :class="{ 'opacity-50': isDragging(asset.id) }"
                            @click.native.stop="$emit('edit-asset', asset)"
                        />
                        <div
                            v-if="isMoving(asset.id)"
                            class="absolute inset-0 flex animate-in fade-in fill-mode-both items-center justify-center [animation-delay:400ms]"
                        >
                            <ui-icon name="loading" class="size-5 text-gray-700 dark:text-gray-200" />
                        </div>
                    </div>
                    <button
                        class="cursor-pointer normal-nums select-none group-hover:text-ui-accent-text/80 dark:group-hover:text-ui-accent-text text-start"
                        @click="$emit('edit-asset', asset)"
                    >
                        {{ isSearching ? asset.path : asset.basename }}
                    </button>
                </div>
            </template>
            <template #prepended-row-actions="{ row: asset }">
                <DropdownItem :text="__(asset.editable ? 'Edit' : 'View')" @click="edit(asset.id)" icon="edit" />
            </template>
        </ListingTable>
    </Card>
</template>

<script>
import AssetBrowserMixin from './AssetBrowserMixin';
import AnimatedFolder from './AnimatedFolder.vue';
import AssetThumbnail from './Thumbnail.vue';
import Breadcrumbs from './Breadcrumbs.vue';
import ItemActions from '@/components/actions/ItemActions.vue';
import {
    Card,
    Dropdown,
    DropdownItem,
    DropdownLabel,
    DropdownMenu,
    DropdownSeparator,
    Editable,
    Panel,
    PanelFooter,
    PanelHeader,
    ListingTable,
} from '@ui';

export default {
    mixins: [AssetBrowserMixin],

    components: {
        AnimatedFolder,
        AssetThumbnail,
        Breadcrumbs,
        Card,
        Dropdown,
        DropdownItem,
        DropdownLabel,
        DropdownMenu,
        DropdownSeparator,
        Editable,
        ItemActions,
        Panel,
        PanelFooter,
        PanelHeader,
        ListingTable,
    },

    props: {
        assets: Array,
        loading: Boolean,
        columns: Array,
        visibleColumns: Array,
        isSearching: Boolean
    },

    watch: {
        creatingFolder(creating) {
            if (creating) {
                this.$nextTick(() => {
                    this.$refs.newFolderInput.$el.scrollIntoView();
                });
            }
        },
    },
};
</script>
