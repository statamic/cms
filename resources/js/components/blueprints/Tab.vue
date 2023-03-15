<template>

    <button
        class="tab-button"
        role="tab"
        :class="{ 'active': isActive }"
        :aria-controls="`tab-section-${tab._id}`"
        :aria-selected="isActive"
        :id="tabId"
        :tabindex="isActive ? 0 : -1"
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
        tabId() {
            return `${this.pascalCase(this.tab.handle)}Tab`;
        },
        pascalCase(handle) {
            return handle
                .split('_')
                .map(word => word.slice(0, 1).toUpperCase() + word.slice(1))
                .join('');
        },
    }

}
</script>
