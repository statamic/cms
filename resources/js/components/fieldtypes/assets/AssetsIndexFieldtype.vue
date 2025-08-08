<template>
    <div class="flex text-2xs">
        <a v-for="asset in value" :key="asset.id" :href="asset.url" target="_blank">
            <asset-thumbnail :asset="asset" class="-my-1 h-8 max-w-3xs" />
        </a>
    </div>
</template>

<script>
import IndexFieldtype from '../IndexFieldtype.vue';
import AssetThumbnail from '../../assets/Browser/Thumbnail.vue';

export default {
    mixins: [IndexFieldtype],
    components: { AssetThumbnail },
};
</script>
