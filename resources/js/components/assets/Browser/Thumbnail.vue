<template>
    <div class="">
        <img
            v-if="asset.thumbnail"
            :src="asset.thumbnail"
            class="asset-thumbnail mx-auto max-h-8 max-w-full rounded-sm"
            loading="lazy"
            :draggable="false"
            :class="{ 'h-8 w-8 object-cover': square }"
        />
        <img
            v-else-if="asset.is_svg"
            :src="asset.url"
            class="asset-thumbnail mx-auto h-8 max-w-full rounded-sm"
            loading="lazy"
            :draggable="false"
        />
        <file-icon v-else :extension="asset.extension" class="asset-thumbnail h-8 w-8 rounded-sm p-px" />
    </div>
</template>

<script>
export default {
    props: {
        asset: Object,
        square: {
            default: false,
            type: Boolean,
        },
    },

    computed: {
        showSvg() {
            return this.asset.extension === 'svg';
        },
    },
};
</script>
