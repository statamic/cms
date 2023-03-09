<template>

    <div class="flex flex-wrap">
        <div
            v-for="(item, i) in value"
            :key="item.id"
            class="relationship-index-field-item"
            :class="{ 'mb-1.5': i < value.length-1 }"
        >
            <div class="flex items-center shrink">
                <div v-if="item.published && item.published !== null"
                    class="little-dot h-1 w-1 mr-1" :class="[item.published ? 'bg-green' : 'bg-gray-400']" />
                <a :href="item.edit_url" :title="item.title" v-text="item.title" />
            </div>
        </div>
    </div>

</template>

<script>
export default {
    mixins: [IndexFieldtype]
}
</script>
