<template>
    <div class="overflow-x-auto overflow-y-hidden">
        <data-list-table
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
                    v-for="(folder, i) in folders"
                    :key="folder.path"
                    v-if="!restrictFolderNavigation"
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
                    <td />
                    <td />

                    <th class="actions-column" :colspan="columns.length">
                        <dropdown-list
                            placement="left-start"
                            v-if="folderActions(folder).length"
                        >
                            <data-list-inline-actions
                                :item="folder.path"
                                :url="folderActionUrl"
                                :actions="folderActions(folder)"
                                @started="actionStarted"
                                @completed="actionCompleted"
                            />
                        </dropdown-list>
                    </th>
                </tr>
            </template>

            <template #cell-basename="{ row: asset, checkboxId }">
                <div class="w-fit-content group flex items-center">
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
                <dropdown-list placement="left-start">
                    <dropdown-item
                        :text="__(canEdit ? 'Edit' : 'View')"
                        @click="edit(asset.id)"
                    />
                    <div class="divider" v-if="asset.actions.length" />
                    <data-list-inline-actions
                        :item="asset.id"
                        :url="actionUrl"
                        :actions="asset.actions"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                </dropdown-list>
            </template>
        </data-list-table>
    </div>
</template>

<script>
import AssetThumbnail from './Thumbnail.vue';
import AssetBrowserMixin from './AssetBrowserMixin';

export default {
    components: {
        AssetThumbnail,
    },

    mixins: [AssetBrowserMixin],

    props: {
        loading: Boolean,
        columns: Array,
    },

    methods: {
        sorted(column, direction) {
            this.$emit('sorted', column, direction);
        },
    },
};
</script>
