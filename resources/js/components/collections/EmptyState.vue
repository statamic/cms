<script setup>
import { computed } from 'vue';
import { Icon } from '@statamic/ui';

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
};
</script>

<template>
    <ui-card-panel :heading="__('Start designing your collection with these steps')" class="max-w-md m-auto">
        <div class="flex flex-wrap">
            <template v-if="canEdit">
                <a :href="cp_url(`collections/${collection.handle}/edit`)" class="w-full p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
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
                <template v-if="blueprints.length">
                    <div class="w-full p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
                        <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                            <Icon name="fieldtype-entries" />
                        </div>
                        <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                            <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ __(createLabel) }}</h3>
                            <p>{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
                            <template v-for="blueprint in blueprints" :key="blueprint.handle">
                                <a :href="getCreateUrl(blueprint.handle)" class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2">
                                    {{ blueprint.title }} →
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <a :href="getCreateUrl()" class="w-full p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
                        <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                            <Icon name="fieldtype-entries" />
                        </div>
                        <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                            <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ __(createLabel) }} →</h3>
                            <p>{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
                        </div>
                    </a>
                </template>
            </template>

            <template v-if="canConfigureFields">
                <a :href="cp_url(`collections/${collection.handle}/blueprints`)" class="w-full p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
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
                <a :href="cp_url(`collections/${collection.handle}/scaffold`)" class="w-full p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
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
    </ui-card-panel>
</template>
