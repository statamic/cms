<template>
    <div class="">
        <img
            v-if="asset.thumbnail"
            :src="asset.thumbnail"
            :alt="alt"
            class="asset-thumbnail mx-auto max-h-8 max-w-full rounded-sm"
            loading="lazy"
            :draggable="false"
            :class="{ 'h-8 w-8 object-cover': square }"
            @error="asset.thumbnail = null"
        />
        <img
            v-else-if="asset.is_svg"
            :src="asset.url"
            :alt="alt"
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
        // Empty by default: the browser renders the filename alongside the
        // thumbnail, so the image is decorative there. Pass a value when the
        // thumbnail appears without an adjacent accessible name.
        alt: {
            default: '',
            type: String,
        },
        square: {
            default: false,
            type: Boolean,
        },
    },
};
</script>
