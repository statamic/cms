<template>

    <div class="asset-table-listing">

        <table v-if="hasResults">

            <thead>
                <tr>
                    <th></th>
                    <th class="title-col">{{ translate('cp.title') }}</th>
                    <th class="size-col extra-col">{{ translate('cp.filesize') }}</th>
                    <th class="modified-col extra-col">{{ translate('cp.date_modified') }}</th>
                    <th class="column-actions"></th>
                </tr>
            </thead>

            <tbody>

                <tr v-if="hasParent && !restrictNavigation">
                    <td>
                        <div class="img">
                            <a @click.prevent="selectFolder(folder.parent_path)">
                                <file-icon extension="folder"></file-icon>
                            </a>
                        </div>
                    </td>
                    <td>
                        <a href="" @click.prevent="selectFolder(folder.parent_path)">..</a>
                    </td>
                    <td colspan="3">..</td>
                </tr>

                <tr is="folderRow"
                    v-for="folder in subfolders"
                    :folder="folder"
                    @open-dropdown="closeDropdowns"
                    @selected="selectFolder"
                    @editing="editFolder"
                    @deleting="deleteFolder"
                    @dropped-on-folder="droppedOnFolder">
                </tr>

                <tr is="assetRow"
                    v-for="asset in assets"
                    :asset="asset"
                    :selected-assets="selectedAssets"
                    @open-dropdown="closeDropdowns"
                    @selected="selectAsset"
                    @deselected="deselectAsset"
                    @editing="editAsset"
                    @deleting="deleteAsset"
                    @assetdragstart="assetDragStart"
                    @doubleclicked="assetDoubleclicked">
                </tr>

            </tbody>
        </table>

    </div>

</template>


<script>
import Listing from './Listing';

export default {

    mixins: [Listing],


    components: {
        AssetRow: require('./AssetRow.vue'),
        FolderRow: require('./FolderRow.vue')
    },


    methods: {
        closeDropdowns: function(context) {
            this.$broadcast('close-dropdown', context);
        },

        droppedOnFolder(folder, e) {
            const asset = e.dataTransfer.getData('asset');
            e.dataTransfer.clearData('asset');

            // discard any drops that weren't started on an asset
            if (asset == '') return;

            this.$emit('assets-dragged-to-folder', folder);
        }

    }

}
</script>
