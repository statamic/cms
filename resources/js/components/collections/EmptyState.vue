<script setup>
import { Icon, EmptyStateMenu, EmptyStateItem } from '@statamic/ui';

const props = defineProps({
    collection: { type: Object, required: true },
    createLabel: { type: String, default: 'Create Entry' },
    blueprints: { type: Array, required: true },
    site: { type: String, required: true },
    svg: { type: String, default: null },
    canEdit: { type: Boolean, default: false },
    canCreate: { type: Boolean, default: false },
    canConfigureFields: { type: Boolean, default: false },
    canStore: { type: Boolean, default: false },
});

function getCreateUrl(blueprint = null) {
    const params = blueprint ? { blueprint } : {};
    return cp_url(`collections/${props.collection.handle}/entries/create/${props.site}`, params);
}
</script>

<template>
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2">
            <Icon name="collections" class="size-5 text-gray-500"></Icon>
            {{ __(collection.title) }}
        </h1>
    </header>

    <EmptyStateMenu :heading="__('Start designing your collection with these steps')">
        <EmptyStateItem
            v-if="canEdit"
            :href="cp_url(`collections/${collection.handle}/edit`)"
            icon="configure-large"
            :heading="__('Configure Collection')"
            :description="__('statamic::messages.collection_next_steps_configure_description')"
        />

        <EmptyStateItem
            v-if="canCreate && blueprints.length > 1"
            icon="fieldtype-entries-large"
            :heading="__(createLabel)"
            :description="__('statamic::messages.collection_next_steps_create_entry_description')"
        >
            <ul class="flex">
                <li v-for="blueprint in blueprints" :key="blueprint.handle">
                    <a :href="getCreateUrl(blueprint.handle)" class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2">
                        {{ blueprint.title }}
                    </a>
                </li>
            </ul>
        </EmptyStateItem>

        <EmptyStateItem
            v-else-if="canCreate"
            :href="getCreateUrl(blueprints[0]?.handle)"
            icon="fieldtype-entries-large"
            :heading="__(createLabel)"
            :description="__('statamic::messages.collection_next_steps_create_entry_description')"
        />

        <EmptyStateItem
            v-if="canConfigureFields"
            :href="cp_url(`collections/${collection.handle}/blueprints`)"
            icon="blueprints-large"
            :heading="__('Configure Blueprints')"
            :description="__('statamic::messages.collection_next_steps_blueprints_description')"
        />

        <EmptyStateItem
            v-if="canStore"
            :href="cp_url(`collections/${collection.handle}/scaffold`)"
            icon="scaffold-large"
            :heading="__('Scaffold Views')"
            :description="__('statamic::messages.collection_next_steps_scaffold_description')"
        />
    </EmptyStateMenu>
</template>
