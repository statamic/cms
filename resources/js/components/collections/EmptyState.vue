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
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2">
            <Icon name="collections" class="size-5 text-gray-500"></Icon>
            {{ __(collection.title) }}
        </h1>
    </header>

    <ui-card-panel :heading="__('Start designing your collection with these steps')" class="max-w-md m-auto">
        <div class="flex flex-wrap [:has(>&)]:p-1.5">
            <template v-if="canEdit">
                <!-- CONFIGURE COLLECTION -->
                <a :href="cp_url(`collections/${collection.handle}/edit`)" class="w-full flex gap-2 px-3 py-4 pt-5 pb-6.5 items-start hover:bg-gray-100 dark:hover:bg-dark-550 rounded-md group">
                    <div class="[&_svg]:size-9 [&_svg]:text-gray-500 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                        <Icon name="configure-large" />
                    </div>
                    <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                        <h3 class="text-lg font-medium trim-text-start text-grey-800 mb-1.5">{{ __('Configure Collection') }}</h3>
                        <p class="text-gray-500 text-sm">{{ __('statamic::messages.collection_next_steps_configure_description') }}</p>
                    </div>
                </a>
            </template>

            <template v-if="canCreate">
                <!-- CREATE ENTRY -->
                <!-- [1] If there's more than one blueprint output it them in a list -->
                <template v-if="blueprints.length > 1">
                    <div class="w-full flex gap-2 px-3 py-4 pb-6.5 items-start hover:bg-gray-100 dark:hover:bg-dark-550 rounded-md group">
                        <div class="[&_svg]:size-9 [&_svg]:text-gray-500 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                            <Icon name="fieldtype-entries-large" />
                        </div>
                        <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                            <h3 class="text-lg font-medium trim-text-start text-grey-800 mb-1.5">{{ __(createLabel) }}</h3>
                            <p class="text-gray-500 text-sm">{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
                            <template v-for="blueprint in blueprints" :key="blueprint.handle">
                                <a :href="getCreateUrl(blueprint.handle)" class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2">
                                    {{ blueprint.title }}
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
                <!-- [/2] Otherwise wrap a link around the container -->
                <template v-else>
                    <a :href="getCreateUrl(blueprints[0]?.handle)" class="w-full flex gap-2 px-3 py-4 pb-6.5 items-start hover:bg-gray-100 dark:hover:bg-dark-550 rounded-md group">
                        <div class="[&_svg]:size-9 [&_svg]:text-gray-500 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                            <Icon name="fieldtype-entries-large" />
                        </div>
                        <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                            <h3 class="text-lg font-medium trim-text-start text-grey-800 mb-1.5">{{ __(createLabel) }}</h3>
                            <p class="text-gray-500 text-sm">{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
                        </div>
                    </a>
                </template>
            </template>

            <template v-if="canConfigureFields">
                <!-- CONFIGURE BLUEPRINTS -->
                <a :href="cp_url(`collections/${collection.handle}/blueprints`)" class="w-full flex gap-2 px-3 py-4 pb-6.5 items-start hover:bg-gray-100 dark:hover:bg-dark-550 rounded-md group">
                    <div class="[&_svg]:size-9 [&_svg]:text-gray-500 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                        <Icon name="blueprints-large" />
                    </div>
                    <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                        <h3 class="text-lg font-medium trim-text-start text-grey-800 mb-1.5">{{ __('Configure Blueprints') }}</h3>
                        <p class="text-gray-500 text-sm">{{ __('statamic::messages.collection_next_steps_blueprints_description') }}</p>
                    </div>
                </a>
            </template>

            <template v-if="canStore">
                <!-- SCAFFOLD VIEWS -->
                <a :href="cp_url(`collections/${collection.handle}/scaffold`)" class="w-full flex gap-2 px-3 py-4 pb-6.5 items-start hover:bg-gray-100 dark:hover:bg-dark-550 rounded-md group">
                    <div class="[&_svg]:size-9 [&_svg]:text-gray-500 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                        <Icon name="scaffold-large" />
                    </div>
                    <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                        <h3 class="text-lg font-medium trim-text-start text-grey-800 mb-1.5">{{ __('Scaffold Views') }}</h3>
                        <p class="text-gray-500 text-sm">{{ __('statamic::messages.collection_next_steps_scaffold_description') }}</p>
                    </div>
                </a>
            </template>

            <div class="hidden first:flex justify-center items-center p-8 w-full">
                <Icon :name="svg || 'empty/content'" />
            </div>
        </div>
    </ui-card-panel>
</template>
