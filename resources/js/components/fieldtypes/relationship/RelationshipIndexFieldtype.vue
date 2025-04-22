<template>
    <div>
        <div class="flex flex-wrap gap-2">
            <ui-badge
                size="sm"
                v-for="item in items"
                :key="item.id"
                :href="item.edit_url"
                :icon="item.icon"
            >
                <ui-status-indicator v-if="item.hasOwnProperty('published') && item.published !== null" :status="item.published" class="h-1" />
                <span v-text="item.title" />
            </ui-badge>
            <ui-dropdown v-if="hasMore && !showingAll">
                <template #trigger>
                    <ui-badge
                        size="sm"
                        as="button"
                        color="white"
                        v-text="__('messages.plus_count_more', { count: value.length - 2 })"
                    />
                </template>
                <ui-dropdown-menu>
                    <!-- TODO: Pass the proper title/label instead of the handle -->
                    <ui-dropdown-label :text="handle" />
                    <!-- TODO: Pass the proper icon instead of hard-coded taxonomies -->
                    <ui-dropdown-item v-for="item in value" icon="taxonomies" :key="item.id" :href="item.edit_url" :text="item.title" />
                </ui-dropdown-menu>
            </ui-dropdown>
        </div>
    </div>
</template>

<script>
import IndexFieldtype from '../IndexFieldtype.vue';

export default {
    mixins: [IndexFieldtype],

    data() {
        return {
            showingAll: false,
        };
    },

    computed: {
        items() {
            return this.showingAll ? this.value : this.value?.slice(0, 2);
        },

        hasMore() {
            return this.value?.length > 2;
        },
    },
};
</script>
