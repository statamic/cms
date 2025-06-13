<template>
    <Panel class="relative overflow-x-auto overscroll-x-contain">
        <PanelHeader class="p-1! flex items-center justify-between">
            <Breadcrumbs v-if="!restrictFolderNavigation" :path="path" @navigated="selectFolder" />
        </PanelHeader>
        <Card inset>
            <data-list-table
                ref="dataListTable"
                :allow-bulk-actions="true"
                :loading="loading"
                :toggle-selection-on-row-click="true"
                contained
                @sorted="sorted"
            >
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
                        @drop="handleFolderDrop(folder); draggingFolder = null"
                        @dragstart="draggingFolder = folder.path"
                        @dragend="draggingFolder = null; draggingFolder = null"
                    >
                        <td />
                        <td v-for="column in visibleColumns">
                            <template v-if="column.field === 'basename'">
                                <a class="group flex cursor-pointer items-center" @click="selectFolder(folder.path)">
                                    <file-icon
                                        extension="folder"
                                        class="inline-block size-8 text-blue-400 group-hover:text-blue-400 me-2"
                                    />
                                    {{ folder.basename }}
                                </a>
                            </template>
                        </td>
                        <td class="actions-column pe-3!">
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
                        <td>
                            <a class="group flex cursor-pointer items-center">
                                <file-icon
                                    extension="folder"
                                    class="group-hover:text-blue-500 inline-block size-8 text-blue-400 me-2"
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
                        <td class="actions-column pe-3!" />
                    </tr>
                </template>

                <template #cell-basename="{ row: asset, checkboxId }">
                    <div
                        class="w-fit group flex items-center"
                        :draggable="canMoveAsset(asset)"
                        @dragover.prevent
                        @dragstart="draggingAsset = asset.id"
                        @dragend="draggingAsset = null"
                    >
                        <asset-thumbnail
                            :asset="asset"
                            :square="true"
                            class="size-8 cursor-pointer me-2"
                            @click.native.stop="$emit('edit-asset', asset)"
                        />
                        <button
                            class="cursor-pointer select-none normal-nums group-hover:text-blue-500"
                            @click="$emit('edit-asset', asset)"
                        >
                            {{ asset.basename }}
                        </button>
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
                                    :icon="action.icon"
                                    :variant="action.dangerous ? 'destructive' : 'default'"
                                    @click="action.run"
                                />
                            </DropdownMenu>
                        </Dropdown>
                    </ItemActions>
                </template>
            </data-list-table>
        </Card>
        <PanelFooter>
            <slot name="footer" />
        </PanelFooter>
    </Panel>
</template>

<script>
import AssetBrowserMixin from './AssetBrowserMixin';
import AssetThumbnail from './Thumbnail.vue';
import Breadcrumbs from './Breadcrumbs.vue';
import ItemActions from '@statamic/components/actions/ItemActions.vue';
import { Card, Dropdown, DropdownItem, DropdownLabel, DropdownMenu, DropdownSeparator, Editable, Panel, PanelFooter, PanelHeader } from '@statamic/ui';

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
    },

    props: {
        loading: Boolean,
        columns: Array,
        visibleColumns: Array,
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
