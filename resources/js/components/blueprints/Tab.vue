<template>

    <button
        class="tab-button"
        role="tab"
        :class="{ 'active': isActive }"
        :aria-selected="isActive"
        :aria-controls="`tab-section-${tab._id}`"
        :id="`tab-${tab._id}`"
        @click="$emit('selected')"
        @mouseenter="$emit('mouseenter')"
    >
        {{ tab.display }}
        <a class="ml-1 cursor-pointer" @click="$emit('removed')" v-if="isActive">
            <span class="-mr-2 text-gray-600 hover:text-gray-800">&times;</span>
        </a>
    </button>

</template>

<script>
export default {

    props: {
        tab: {
            type: Object,
            required: true,
        },
        currentTab: {
            type: String,
            required: true,
        },
    },
    computed: {
        isActive() {
            return this.currentTab === this.tab._id;
        },
    }

}
</script>
