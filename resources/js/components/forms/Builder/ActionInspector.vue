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
    <div>
        <!-- Mobile -->
        <div class="right-panel-popover min-[1000px]:hidden">
            <div id="popover-right-panel" class="right-panel-popover__menu" popover>
                <button class="right-panel-popover__close-button" :title="__('Close')" popovertarget="popover-right-panel">
                    <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                </button>
                <div class="@container pt-6 pb-40 px-2.5">
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
                                    <Input v-model="page.button_label" :placeholder="submitButtonPlaceholder" />
                                </Field>

                                <Field v-if="!isFirstPage" :label="__('Previous Button Label')">
                                    <Input v-model="page.previous_page_label"  :placeholder="__('Previous Page')" />
                                </Field>
                            </div>
                        </TabContent>
                    </Tabs>
                </div>
            </div>
        </div>

        <!-- Desktop -->
        <div class="@container relative pt-6 pb-12 px-2.5 pe-4.5 max-[1000px]:hidden">
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
                            <Input v-model="page.button_label" :placeholder="submitButtonPlaceholder" />
                        </Field>

                        <Field v-if="!isFirstPage" :label="__('Previous Button Label')">
                            <Input v-model="page.previous_page_label" :placeholder="__('Previous Page')" />
                        </Field>
                    </div>
                </TabContent>
            </Tabs>
        </div>
    </div>
</template>
