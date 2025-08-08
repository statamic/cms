<template>
    <div>
        <div class="flex flex-wrap gap-2">
            <Badge size="sm" v-for="item in items" :key="item.id" :href="item.edit_url" :icon="item.icon">
                <StatusIndicator
                    v-if="item.hasOwnProperty('published') && item.published !== null"
                    :status="item.published"
                    class="h-1"
                />
                <span v-text="item.title" />
            </Badge>
            <Dropdown v-if="hasMore && !showingAll">
                <template #trigger>
                    <Badge
                        size="sm"
                        as="button"
                        color="white"
                        v-text="__('messages.plus_count_more', { count: value.length - 2 })"
                    />
                </template>
                <DropdownMenu>
                    <!-- TODO: Pass the proper title/label instead of the handle -->
                    <DropdownLabel :text="handle" />
                    <!-- TODO: Pass the proper icon instead of hard-coded taxonomies -->
                    <DropdownItem
                        v-for="item in value"
                        icon="taxonomies"
                        :key="item.id"
                        :href="item.edit_url"
                        :text="item.title"
                    />
                </DropdownMenu>
            </Dropdown>
        </div>
    </div>
</template>

<script>
import IndexFieldtype from '../IndexFieldtype.vue';
import { Badge, StatusIndicator, Dropdown, DropdownMenu, DropdownLabel, DropdownItem } from '@statamic/ui';

export default {
    mixins: [IndexFieldtype],

    components: {
        Badge,
        StatusIndicator,
        Dropdown,
        DropdownMenu,
        DropdownLabel,
        DropdownItem,
    },

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
