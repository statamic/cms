<template>
    <Panel class="relative overflow-x-auto overscroll-x-contain">
        <data-list-table
            ref="dataListTable"
            :allow-bulk-actions="true"
            :loading="loading"
            :toggle-selection-on-row-click="true"
            @sorted="sorted"
        >
            <template #tbody-start>
                <tr
                    v-if="!restrictFolderNavigation"
                    v-for="(folder, i) in folders"
                    :key="folder.path"
                    class="pointer-events-auto"
                    :class="{ 'bg-blue-50': dragOverFolder === folder.path }"
                    :draggable="canMoveFolder(folder)"
                    @dragover.prevent="dragOverFolder = folder.path"
                    @dragleave.prevent="dragOverFolder = null"
                    @drop="handleFolderDrop(folder); dragOverFolder = null"
                    @dragstart="draggingFolder = folder.path"
                    @dragend="draggingFolder = null; dragOverFolder = null"
                >
                    <td />
                    <td v-for="column in visibleColumns">
                        <template v-if="column.field === 'basename'">
                            <a class="group flex cursor-pointer items-center" @click="selectFolder(folder.path)">
                                <file-icon
                                    extension="folder"
                                    class="inline-block h-8 w-8 text-blue-400 group-hover:text-blue ltr:mr-2 rtl:ml-2"
                                />
                                {{ folder.basename }}
                            </a>
                        </template>
                    </td>
                    <td class="actions-column pr-3!">
                        <ItemActions
                            :url="actionUrl"
                            :actions="folder.actions"
                            :item="folder.path"
                            @started="actionStarted"
                            @completed="actionCompleted"
                            v-slot="{ actions }"
                        >
                            <Dropdown placement="left-start" v-if="folderActions(folder).length">
                                <DropdownMenu>
                                    <DropdownLabel :text="__('Actions')" />
                                    <DropdownItem
                                        v-for="action in actions"
                                        :key="action.handle"
                                        :text="__(action.title)"
                                        icon="edit"
                                        :class="{ 'text-red-500': action.dangerous }"
                                        @click="action.run"
                                    />
                                </DropdownMenu>
                            </Dropdown>
                        </ItemActions>
                    </td>
                </tr>
                <tr v-if="creatingFolder">
                    <td />
                    <td>
                        <a class="group flex cursor-pointer items-center">
                            <file-icon
                                extension="folder"
                                class="group-hover:text-blue inline-block h-8 w-8 text-blue-400 ltr:mr-2 rtl:ml-2"
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
                    <td :colspan="columns.length - 1" />
                    <td class="actions-column pr-3!" />
                </tr>
            </template>

            <template #cell-basename="{ row: asset, checkboxId }">
                <div
                    class="w-fit-content group flex items-center"
                    :draggable="canMoveAsset(asset)"
                    @dragover.prevent
                    @dragstart="draggingAsset = asset.id"
                    @dragend="draggingAsset = null"
                >
                    <asset-thumbnail
                        :asset="asset"
                        :square="true"
                        class="h-8 w-8 cursor-pointer ltr:mr-2 rtl:ml-2"
                        @click.native.stop="$emit('edit-asset', asset)"
                    />
                    <label
                        :for="checkboxId"
                        class="cursor-pointer select-none normal-nums group-hover:text-blue"
                        @click.stop="$emit('edit-asset', asset)"
                    >
                        {{ asset.basename }}
                    </label>
                </div>
            </template>

            <template #actions="{ row: asset }">
                <ItemActions
                    :url="actionUrl"
                    :actions="asset.actions"
                    :item="asset.id"
                    @started="actionStarted"
                    @completed="actionCompleted"
                    v-slot="{ actions }"
                >
                    <Dropdown placement="left-start" class="me-3">
                        <DropdownMenu>
                            <DropdownLabel :text="__('Actions')" />
                            <DropdownItem
                                :text="__(canEdit ? 'Edit' : 'View')"
                                @click="edit(asset.id)"
                                icon="edit"
                            />
                            <DropdownSeparator v-if="asset.actions.length" />
                            <DropdownItem
                                v-for="action in actions"
                                :key="action.handle"
                                :text="__(action.title)"
                                icon="edit"
                                :class="{ 'text-red-500': action.dangerous }"
                                @click="action.run"
                            />
                        </DropdownMenu>
                    </Dropdown>
                </ItemActions>
            </template>
        </data-list-table>
        <ui-panel-footer class="p-1! pb-0!">
            <Breadcrumbs :path="path" @navigated="selectFolder" />
        </ui-panel-footer>
    </Panel>
</template>

<script>
import AssetThumbnail from './Thumbnail.vue';
import Breadcrumbs from './Breadcrumbs.vue';
import AssetBrowserMixin from './AssetBrowserMixin';
import { Panel, Dropdown, DropdownMenu, DropdownItem, DropdownLabel, DropdownSeparator, Editable } from '@statamic/ui';
import ItemActions from '@statamic/components/actions/ItemActions.vue';

export default {
    mixins: [AssetBrowserMixin],

    components: {
        ItemActions,
        Editable,
        AssetThumbnail,
        Breadcrumbs,
        Panel,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownLabel,
        DropdownSeparator,
    },

    props: {
        loading: Boolean,
        columns: Array,
        visibleColumns: Array,
    },

    data() {
        return {
            dragOverFolder: null,
        };
    },

    computed: {
        assets() {
            return this.$refs.dataListTable.rows;
        },
    },

    methods: {
        sorted(column, direction) {
            this.$emit('sorted', column, direction);
        },
    },

    watch: {
        creatingFolder(creating) {
            if (creating) {
                this.$nextTick(() => {
                    this.$refs.newFolderInput.$el.scrollIntoView();
                });
            }
        },
    }
};
</script>
