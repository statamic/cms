<template>

    <div class="rounded overflow-hidden">

        <div
            v-if="showSvg"
            class="w-full h-full"
            :style="{ backgroundImage: `url('${asset.url}')` }"
        />

        <template v-else>
            <img v-if="asset.is_image" :src="asset.thumbnail" class="w-full h-full" />
            <file-icon v-else :extension="asset.extension"></file-icon>
        </template>

    </div>

</template>

<script>
export default {

    props: ['asset'],

    computed: {

        showSvg() {
            return this.asset.extension === 'svg' && !this.asset.url.includes(':');
        }

    }

}
</script>
