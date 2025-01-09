<template>

    <div class="text-2xs flex">
        <a
            v-for="asset in value"
            :key="asset.id"
            :href="asset.url"
            target="_blank"
        >
            <asset-thumbnail :asset="asset" class="h-8 max-w-3xs -my-1" />
        </a>
    </div>

</template>

<script>
import IndexFieldtype from '../IndexFieldtype.vue';
import AssetThumbnail from '../../assets/Browser/Thumbnail.vue';

export default {
    mixins: [IndexFieldtype],
    components: {AssetThumbnail}
}
</script>
