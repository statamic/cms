<script>
import Listing from '../Listing.vue';
import { Widget, Badge } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        Badge,
        Widget,
    },

    props: {
        count: Number,
        hasStatamicUpdate: Boolean,
        updatableAddons: Object,
    },

    data() {
        return {
            cols: [
                { label: 'Package', field: 'name', visible: true },
                { label: 'Current', field: 'current_version', visible: true },
                { label: 'Latest', field: 'latest_version', visible: true },
            ],
            items: [],
            currentPage: 1,
        };
    },

    methods: {
        getItems() {
            const items = [];

            if (this.hasStatamicUpdate) {
                items.push({ name: 'Statamic Core', type: 'core', slug: 'statamic' });
            }

            Object.entries(this.updatableAddons || {}).forEach(([slug, name]) => {
                items.push({ name, type: 'addon', slug });
            });

            return items;
        },
    },

    created() {
        this.items = this.getItems();
    },
};
</script>

<template>
    <Widget :title="__('Updates')" icon="updates">
        <data-list v-if="items.length" :rows="items" :columns="cols" :sort="false">
            <data-list-table unstyled class="[&_td]:p-0.5 [&_td]:text-sm [&_thead]:hidden">
                <template #cell-name="{ row: update }">
                    <a :href="cp_url(`updater/${update.slug}`)" class="flex items-center gap-2">
                        <span>{{ update.name.name || update.name }}</span>
                        <Badge size="sm" pill color="green" text="2" />
                    </a>
                </template>
            </data-list-table>
        </data-list>

        <p v-else class="p-3 text-center text-sm text-gray-600">
            {{ __('Everything is up to date.') }}
        </p>

        <template #actions>
            <data-list-pagination
                v-if="meta.last_page != 1"
                :resource-meta="meta"
                @page-selected="selectPage"
                :scroll-to-top="false"
                :show-page-links="false"
            />
            <slot name="actions" />
        </template>
    </Widget>
</template>
