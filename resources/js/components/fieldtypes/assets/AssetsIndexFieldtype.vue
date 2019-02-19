<template>

    <div class="text-2xs">
        <a
            v-for="asset in value"
            :key="asset.id"
            :href="asset.url"
            target="_blank"
        >
            <asset-thumbnail :asset="asset" class="w-6 h-6 mr-1" />
        </a>
    </div>

</template>

<script>
import AssetThumbnail from '../../assets/Browser/Thumbnail.vue';

export default {
    mixins: [IndexFieldtype],
    components: {AssetThumbnail}
}
</script>
