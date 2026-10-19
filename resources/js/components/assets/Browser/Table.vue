<template>
    <Card inset variant="flat" class="[&_tr:has([data-moving])]:pointer-events-none">
        <ListingTable contained>
            <template #tbody-start>
                <tr
                    v-if="!restrictFolderNavigation"
                    v-for="(folder, i) in folders"
                    :key="folder.path"
                    class="pointer-events-auto"
                    :class="{ 'pointer-events-none': isMoving(folder.path) }"
                    :draggable="canMoveFolder(folder)"
                    @dragover.prevent="dragOverFolder = folder.path"
                    @dragleave.prevent="!$event.currentTarget.contains($event.relatedTarget) && (dragOverFolder = null)"
                    @drop="
                        handleFolderDrop(folder);
                        dragOverFolder = null;
                    "
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
                                :class="{
                                    'ring-2 ring-blue-400': dragOverFolder === folder.path,
                                    'text-gray-400 dark:text-gray-500': isMoving(folder.path),
                                }"
                                @click="selectFolder(folder.path)"
                            >
                                <!-- Icons load asynchronously, so reserve the space to stop the row jumping. -->
                                <div v-if="isMoving(folder.path)" class="me-2 flex size-8 shrink-0 items-center justify-center">
                                    <ui-icon name="loading" class="size-5 text-gray-700 dark:text-gray-200" />
                                </div>
                                <file-icon
                                    v-else
                                    extension="folder"
                                    class="me-2 inline-block size-8 shrink-0 text-blue-400/90 group-hover:text-blue-400"
                                />
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
                    :draggable="true"
                    @dragover.prevent
                    @dragstart="assetDragStarted($event, asset)"
                    @dragend="draggingAsset = null"
                    :data-moving="isMoving(asset.id) || null"
                    :class="{ 'text-gray-400 dark:text-gray-500': isDragging(asset.id) || isMoving(asset.id) }"
                >
                    <!-- Icons load asynchronously, so reserve the space to stop the row jumping. -->
                    <div v-if="isMoving(asset.id)" class="flex size-8 shrink-0 items-center justify-center">
                        <ui-icon name="loading" class="size-5 text-gray-700 dark:text-gray-200" />
                    </div>
                    <asset-thumbnail
                        v-else
                        :asset="asset"
                        alt=""
                        :square="true"
                        class="size-8 cursor-pointer"
                        :class="{ 'opacity-50': isDragging(asset.id) }"
                        @click.native.stop="$emit('edit-asset', asset)"
                    />
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

    data() {
        return {
            dragOverFolder: null,
        };
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
