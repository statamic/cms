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
                <tr v-if="folder && folder.parent_path && !restrictFolderNavigation">
                    <td />
                    <td @click="selectFolder(folder.parent_path)">
                        <a class="group flex cursor-pointer items-center">
                            <file-icon
                                extension="folder"
                                class="inline-block h-8 w-8 text-blue-400 group-hover:text-blue ltr:mr-2 rtl:ml-2"
                            />
                            ..
                        </a>
                    </td>
                    <td :colspan="columns.length" />
                </tr>
                <tr
                    v-if="!restrictFolderNavigation"
                    v-for="(folder, i) in folders"
                    :key="folder.path"
                    class="pointer-events-auto"
                    :draggable="canMoveFolder(folder)"
                    @dragover.prevent
                    @drop="handleFolderDrop(folder)"
                    @dragstart="draggingFolder = folder.path"
                    @dragend="draggingFolder = null"
                >
                    <td />
                    <td @click="selectFolder(folder.path)">
                        <a class="group flex cursor-pointer items-center">
                            <file-icon
                                extension="folder"
                                class="inline-block h-8 w-8 text-blue-400 group-hover:text-blue ltr:mr-2 rtl:ml-2"
                            />
                            {{ folder.basename }}
                        </a>
                    </td>
                    <td :colspan="columns.length - 1" />
                    <td class="actions-column pr-3!">
                        <Dropdown placement="left-start" v-if="folderActions(folder).length">
                            <DropdownMenu>
                                <DropdownLabel :text="__('Actions')" />
                                <data-list-inline-actions
                                    :item="folder.path"
                                    :url="folderActionUrl"
                                    :actions="folderActions(folder)"
                                    @started="actionStarted"
                                    @completed="actionCompleted"
                                />
                            </DropdownMenu>
                        </Dropdown>
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
                <Dropdown placement="left-start" class="me-3">
                    <DropdownMenu>
                        <DropdownLabel :text="__('Actions')" />
                        <DropdownItem
                            :text="__(canEdit ? 'Edit' : 'View')"
                            @click="edit(asset.id)"
                            icon="edit"
                        />
                        <DropdownSeparator v-if="asset.actions.length" />
                        <data-list-inline-actions
                            :item="asset.id"
                            :url="actionUrl"
                            :actions="asset.actions"
                            @started="actionStarted"
                            @completed="actionCompleted"
                        />
                    </DropdownMenu>
                </Dropdown>
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

export default {
    mixins: [AssetBrowserMixin],

    components: {
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
