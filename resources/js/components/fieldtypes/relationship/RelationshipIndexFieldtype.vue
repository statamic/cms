<template>

    <div class="text-2xs">
        <div
            v-for="(item, i) in value"
            :key="item.id"
            class="flex items-center"
            :class="{ 'mb-1': i < value.length-1 }"
        >
            <div v-if="item.published && item.published !== null"
                class="little-dot mr-1" :class="[item.published ? 'bg-green' : 'bg-grey-40']" />
            <a :href="item.edit_url">{{ item.title }}</a>
        </div>
    </div>

</template>

<script>
export default {
    mixins: [IndexFieldtype]
}
</script>
