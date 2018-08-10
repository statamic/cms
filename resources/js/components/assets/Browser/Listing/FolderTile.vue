<template>

    <div class="asset-tile is-folder"
         @click.prevent="selectFolder(folder.path)">

        <div class="asset-thumb-container">
            <file-icon extension="folder"></file-icon>
        </div>

        <div class="asset-meta">
            <div class="asset-filename" :title="label">{{ folder.title }}</div>
        </div>

    </div>

</template>


<script>
import Folder from './Folder';

export default {

    mixins: [Folder]

}
</script>
