<template>

    <div class="asset-grid-listing"
        v-if="hasParent && !restrictNavigation || (!isSearching || (isSearching && hasResults))">

        <div class="asset-tile is-folder"
             v-if="hasParent && !restrictNavigation"
             @click.prevent="selectFolder(folder.parent_path)">
            <div class="asset-thumb-container">
                <file-icon extension="folder"></file-icon>
            </div>
            <div class="asset-meta">
                <div class="asset-filename">..</div>
            </div>
        </div>

        <folder-tile
            v-for="(folder, i) in subfolders"
            :key="i"
            :folder="folder"
            @selected="selectFolder"
            @editing="editFolder"
            @deleting="deleteFolder">
        </folder-tile>

        <asset-tile
            v-for="asset in assets"
            :key="asset.id"
            :asset="asset"
            :selected-assets="selectedAssets"
            @selected="selectAsset"
            @deselected="deselectAsset"
            @editing="editAsset"
            @doubleclicked="assetDoubleclicked">
        </asset-tile>

    </div>

</template>


<script>
import Listing from './Listing';

export default {

    mixins: [Listing],


    components: {
        AssetTile: require('./AssetTile.vue'),
        FolderTile: require('./FolderTile.vue')
    },

}
</script>
