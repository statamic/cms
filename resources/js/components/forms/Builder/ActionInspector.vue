<script setup lang="ts">
import { Field, Icon, Input, TabContent, TabList, Tabs, TabTrigger } from '@ui';
import { computed, ref, watch } from 'vue';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';
import { __ } from '@/bootstrap/globals';

const { dirty, inspecting: page, pages } = injectBuilderContext();

enum ActionInspectorTabs {
    Settings = 'settings',
}

const activeTab = ref<ActionInspectorTabs>(ActionInspectorTabs.Settings);

const isFirstPage = computed(() => pages.value.findIndex((p) => p._id === page.value._id) === 0);
const isLastPage = computed(() => pages.value.findIndex((p) => p._id === page.value._id) === pages.value.length - 1);

const title = computed(() => isLastPage.value ? __('Submit button') : __('Next Page button'));
const submitButtonLabel = computed(() => isLastPage.value ? __('Submit Button Label') : __('Next Button Label'));
const submitButtonPlaceholder = computed(() => isLastPage.value ? __('Submit') : __('Next Page'));

watch(() => page.value.button_label, dirty);
watch(() => page.value.previous_page_label, dirty);
</script>

<template>
    <div class="@container relative pt-6 pb-40 min-[1000px]:pb-12 px-2.5 min-[1000px]:pe-4.5">
        <Tabs v-model:modelValue="activeTab" :unmount-on-hide="false">
            <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                <TabTrigger :name="ActionInspectorTabs.Settings" :text="__('Settings')" />
            </TabList>

            <TabContent :name="ActionInspectorTabs.Settings">
                <div class="space-y-6 pt-8">
                    <div class="flex items-center gap-2.5">
                        <div class="size-4">
                            <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                        </div>
                        <a :href="`#actions-${page._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                            {{ title }}
                            <div class="grid *:[grid-area:1/1]">
                                <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                            </div>
                        </a>
                    </div>

                    <Field :label="submitButtonLabel">
                        <Input v-model="page.button_label" :placeholder="submitButtonPlaceholder" focus />
                    </Field>

                    <Field v-if="!isFirstPage" :label="__('Previous Button Label')">
                        <Input v-model="page.previous_page_label" :placeholder="__('Previous Page')" />
                    </Field>
                </div>
            </TabContent>
        </Tabs>
    </div>
</template>
