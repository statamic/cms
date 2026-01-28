<template>
    <div class="flex text-2xs gap-2">
        <a v-for="asset in value.slice(0, 5)" :key="asset.id" :href="asset.url" target="_blank">
            <asset-thumbnail :asset="asset" class="-my-1 h-8 max-w-3xs" />
        </a>
        <span
            v-if="value.length > 5"
            class="-my-1 flex h-8 min-w-8 items-center justify-center px-1.5 font-mono text-gray-600 dark:text-gray-400"
        >
            +&thinsp;{{ value.length - 5 }}
        </span>
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
