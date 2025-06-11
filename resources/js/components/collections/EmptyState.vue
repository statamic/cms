<template>
    <div class="card p-4 content">
        <div class="flex flex-wrap">
            <template v-if="canEdit">
                <a :href="editUrl" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
                    <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                        <Icon name="configure" />
                    </div>
                    <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                        <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ __('Configure Collection') }} →</h3>
                        <p>{{ __('statamic::messages.collection_next_steps_configure_description') }}</p>
                    </div>
                </a>
            </template>

            <template v-if="canCreate">
                <template v-if="multipleBlueprints">
                    <div class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
                        <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                            <Icon name="fieldtype-blueprints" />
                        </div>
                        <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                            <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ createLabel }}</h3>
                            <p>{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
                            <template v-for="blueprint in blueprints" :key="blueprint.handle">
                                <a :href="createUrl(blueprint.handle)" class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2">
                                    {{ blueprint.title }} →
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <a :href="createUrl()" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
                        <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                            <Icon name="fieldtype-entries" />
                        </div>
                        <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                            <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ createLabel }} →</h3>
                            <p>{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
                        </div>
                    </a>
                </template>
            </template>

            <template v-if="canConfigureFields">
                <a :href="blueprintsUrl" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
                    <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                        <Icon name="fieldtype-blueprints" />
                    </div>
                    <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                        <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ __('Configure Blueprints') }} →</h3>
                        <p>{{ __('statamic::messages.collection_next_steps_blueprints_description') }}</p>
                    </div>
                </a>
            </template>

            <template v-if="canStore">
                <a :href="scaffoldUrl" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
                    <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                        <Icon name="scaffold" />
                    </div>
                    <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                        <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ __('Scaffold Views') }} →</h3>
                        <p>{{ __('statamic::messages.collection_next_steps_scaffold_description') }}</p>
                    </div>
                </a>
            </template>

            <div class="hidden first:flex justify-center items-center p-8 w-full">
                <Icon :name="svg || 'empty/content'" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Icon } from '@statamic/ui';

const props = defineProps({
    collection: { type: Object, required: true },
    site: { type: String, required: true },
    svg: { type: String, default: null },
    canEdit: { type: Boolean, default: false },
    canCreate: { type: Boolean, default: false },
    canConfigureFields: { type: Boolean, default: false },
    canStore: { type: Boolean, default: false },
});

// Ensure we have access to the collection methods
const collection = computed(() => ({
    ...props.collection,
    handle: props.collection.handle,
    createLabel: props.collection.createLabel || __('Create Entry'),
    entryBlueprints: () => props.collection.entryBlueprints || []
}));

const editUrl = computed(() => cp_url(`collections/${collection.value.handle}/edit`));
const blueprintsUrl = computed(() => cp_url(`collections/${collection.value.handle}/blueprints`));
const scaffoldUrl = computed(() => cp_url(`collections/${collection.value.handle}/scaffold`));
const createLabel = computed(() => collection.value.createLabel);
const multipleBlueprints = computed(() => (collection.value.entryBlueprints() || []).length > 1);
const blueprints = computed(() => collection.value.entryBlueprints() || []);

const createUrl = (blueprintHandle = null) => {
    const params = blueprintHandle ? { blueprint: blueprintHandle } : {};
    return cp_url(`collections/${collection.value.handle}/entries/create/${props.site}`, params);
};
</script> 