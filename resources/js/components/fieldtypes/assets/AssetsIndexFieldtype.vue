<template>

    <div class="text-2xs flex">
        <a
            v-for="asset in value.assets.slice(0, value.total > 6 ? 5 : 6)"
            :key="asset.id"
            :href="asset.url"
            target="_blank"
        >
            <asset-thumbnail :asset="asset" class="h-8 max-w-3xs -my-1" />
        </a>
        <span
            v-if="value.total > 6"
            class="-my-1 flex h-8 min-w-8 items-center justify-center px-1.5 font-mono text-gray-600 dark:text-gray-400"
        >
            +&thinsp;{{ value.total - 5 }}
        </span>
    </div>

</template>

<script>
import AssetThumbnail from '../../assets/Browser/Thumbnail.vue';

export default {
    mixins: [IndexFieldtype],
    components: {AssetThumbnail}
}
</script>
