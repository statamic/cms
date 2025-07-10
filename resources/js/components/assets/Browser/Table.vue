<template>
    <Card inset>
        <ListingTable contained>
            <template #tbody-start>
                <tr
                    v-if="!restrictFolderNavigation"
                    v-for="(folder, i) in folders"
                    :key="folder.path"
                    class="pointer-events-auto"
                    :class="{ 'bg-blue-50': draggingFolder === folder.path }"
                    :draggable="canMoveFolder(folder)"
                    @dragover.prevent="draggingFolder = folder.path"
                    @dragleave.prevent="draggingFolder = null"
                    @drop="
                        handleFolderDrop(folder);
                        draggingFolder = null;
                    "
                    @dragstart="draggingFolder = folder.path"
                    @dragend="
                        draggingFolder = null;
                        draggingFolder = null;
                    "
                >
                    <td />
                    <td v-for="column in visibleColumns">
                        <template v-if="column.field === 'basename'">
                            <a class="group flex cursor-pointer items-center" @click="selectFolder(folder.path)">
                                <file-icon
                                    extension="folder"
                                    class="me-2 inline-block size-8 text-blue-400 group-hover:text-blue-400"
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
                    <td :colspan="columns.length - 1">
                        <a class="group flex cursor-pointer items-center">
                            <file-icon
                                extension="folder"
                                class="me-2 inline-block size-8 text-blue-400 group-hover:text-blue-500"
                            />
                            <Editable
                                ref="newFolderInput"
                                v-model:modelValue="newFolderName"
                                :start-with-edit-mode="true"
                                submit-mode="enter"
                                :placeholder="__('New Folder')"
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
                    class="group flex w-fit items-center"
                    :draggable="true"
                    @dragover.prevent
                    @dragstart="draggingAsset = asset.id"
                    @dragend="draggingAsset = null"
                >
                    <asset-thumbnail
                        :asset="asset"
                        :square="true"
                        class="me-2 size-8 cursor-pointer"
                        @click.native.stop="$emit('edit-asset', asset)"
                    />
                    <button
                        class="cursor-pointer normal-nums select-none group-hover:text-blue-500"
                        @click="$emit('edit-asset', asset)"
                    >
                        {{ asset.basename }}
                    </button>
                </div>
            </template>
            <template #prepended-row-actions="{ row: asset }">
                <DropdownItem :text="__(canEdit ? 'Edit' : 'View')" @click="edit(asset.id)" icon="edit" />
            </template>
        </ListingTable>
    </Card>
</template>

<script>
import AssetBrowserMixin from './AssetBrowserMixin';
import AssetThumbnail from './Thumbnail.vue';
import Breadcrumbs from './Breadcrumbs.vue';
import ItemActions from '@statamic/components/actions/ItemActions.vue';
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
} from '@statamic/ui';

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
        loading: Boolean,
        columns: Array,
        visibleColumns: Array,
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
