<template>

    <div class="">
        <div v-if="showSvg" class="rounded p-1 mb-1 flex items-center justify-center asset-thumbnail h-full w-full">
            <img
                class="max-w-full max-h-full mx-auto"
                :src="asset.url"
            />
        </div>

        <template v-else>
            <img v-if="asset.is_image"
                :data-src="asset.thumbnail"
                class="asset-thumbnail max-h-full max-w-full mx-auto rounded lazyload"
                :class="{'w-8 h-8 fit-cover': square}"
                />
            <file-icon v-else :extension="asset.extension" class="h-full w-full p-1"></file-icon>
        </template>

    </div>

</template>

<script>
require('lazysizes')

export default {

    props: {
        'asset': Object,
        'square': {
            default: false,
            type: Boolean
        }
    },

    computed: {

        showSvg() {
            return this.asset.extension === 'svg';
        }

    }

}
</script>
