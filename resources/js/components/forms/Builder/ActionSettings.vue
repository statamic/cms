<script setup>
import { Field, Icon, Input, Tabs, TabList, TabTrigger, TabContent } from '@ui';
import { computed, ref } from 'vue';

const activeTab = ref('settings');

const inspectorTarget = ref('action_submit');
const nextPageButtonLabel = ref(__('Next Page'));
const previousPageButtonLabel = ref(__('Previous Page'));
const submitButtonLabel = ref(__('Submit'));

const selectedActionButtonHeading = computed(() => (
    inspectorTarget.value === 'action_submit'
        ? __('Submit button')
        : __('Next Page button')
));

const selectedActionButtonLabel = computed({
    get() {
        return inspectorTarget.value === 'action_submit' ? submitButtonLabel.value : nextPageButtonLabel.value;
    },
    set(value) {
        if (inspectorTarget.value === 'action_submit') {
            submitButtonLabel.value = value;
            return;
        }

        nextPageButtonLabel.value = value;
    },
});

const selectedActionPrimaryLabel = computed(() => (
    inspectorTarget.value === 'action_submit'
        ? __('Submit Button Label')
        : __('Next Button Label')
));

const selectedActionButtonAnchor = computed(() => (
    inspectorTarget.value === 'action_submit' ? '#action-submit-button' : '#action-next-button'
));
</script>

<template>
    <div>
        <div class="right-panel-popover min-[1000px]:hidden">
            <div id="popover-right-panel" class="right-panel-popover__menu" popover>
                <button class="right-panel-popover__close-button" title="Close" popovertarget="popover-right-panel">
                    <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                </button>
                <div class="@container pt-6 pb-40 px-2.5">
                    <Tabs v-model:modelValue="activeTab" :unmount-on-hide="false">
                        <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                            <TabTrigger name="settings" :text="__('Settings')" />
                        </TabList>

                        <TabContent name="settings">
                            <div class="space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <a :href="selectedActionButtonAnchor" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                        {{ selectedActionButtonHeading }}
                                        <div class="grid *:[grid-area:1/1]">
                                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                        </div>
                                    </a>
                                </div>
                                <Field :label="selectedActionPrimaryLabel">
                                    <Input v-model="selectedActionButtonLabel" />
                                </Field>
                                <Field v-if="inspectorTarget === 'action_next'" :label="__('Previous Button Label')">
                                    <Input v-model="previousPageButtonLabel" />
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
                    <TabTrigger name="settings" :text="__('Settings')" />
                </TabList>

                <TabContent name="settings">
                    <div class="space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="selectedActionButtonAnchor" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ selectedActionButtonHeading }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>
                        <Field :label="selectedActionPrimaryLabel">
                            <Input v-model="selectedActionButtonLabel" />
                        </Field>
                        <Field v-if="inspectorTarget === 'action_next'" :label="__('Previous Button Label')">
                            <Input v-model="previousPageButtonLabel" />
                        </Field>
                    </div>
                </TabContent>
            </Tabs>
        </div>
    </div>
</template>
