<script setup>
import { Badge, Button, Field, Icon, Input, TabContent, TabList, Tabs, TabTrigger, Textarea, PublishContainer, PublishFieldsProvider, PublishFields } from '@ui';

defineProps({
    fieldtype: Object,
});
</script>

<template>
    <div>
<!--        <div class="right-panel-popover min-[1000px]:hidden">-->
<!--            <div id="popover-right-panel" class="right-panel-popover__menu" popover>-->
<!--                <button class="right-panel-popover__close-button" title="Close" popovertarget="popover-right-panel">-->
<!--                    <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>-->
<!--                </button>-->
<!--                <div class="@container pt-6 pb-40 px-2.5">-->
<!--                    <Tabs v-model:modelValue="activeTab" :unmount-on-hide="false">-->
<!--                        <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">-->
<!--                            <TabTrigger name="settings" :text="__('Settings')" />-->
<!--                            <TabTrigger name="conditions" :text="__('Logic')" />-->
<!--                            <TabTrigger name="validation" :text="__('Validation')" />-->
<!--                        </TabList>-->

<!--                        <TabContent name="settings">-->
<!--                            <div class="space-y-6 pt-8">-->
<!--                                <div class="flex items-center gap-2.5">-->
<!--                                    <div class="size-4">-->
<!--                                        <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />-->
<!--                                    </div>-->
<!--                                    <a href="#editing-field" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">-->
<!--                                        <span class="truncate">{{ settingsLabel }}</span>-->
<!--                                        <div class="grid *:[grid-area:1/1]">-->
<!--                                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />-->
<!--                                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />-->
<!--                                        </div>-->
<!--                                    </a>-->
<!--                                </div>-->

<!--                                <Field :label="__('Label')">-->
<!--                                    <Input v-model="settingsLabel" />-->
<!--                                </Field>-->

<!--                                <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">-->
<!--                                    <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />-->
<!--                                </Field>-->

<!--                                <Field :label="__('Placeholder')">-->
<!--                                    <Input v-model="settingsPlaceholder" />-->
<!--                                </Field>-->

<!--                                <Field :label="__('Character Limit')" :instructions="__('Set the recommended maximum number of enterable characters.')">-->
<!--                                    <Input v-model="settingsCharacterLimit" type="number" />-->
<!--                                </Field>-->

<!--                                <Field :label="__('Options')">-->
<!--                                    <TableFieldtype-->
<!--                                        handle="options"-->
<!--                                        v-model:value="optionRows"-->
<!--                                        :config="optionRowsConfig"-->
<!--                                    />-->
<!--                                </Field>-->
<!--                            </div>-->
<!--                        </TabContent>-->

<!--                        <TabContent name="conditions">-->
<!--                            <div class="space-y-6 pt-8">-->
<!--                                <div class="flex items-center gap-2.5">-->
<!--                                    <div class="size-4">-->
<!--                                        <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />-->
<!--                                    </div>-->
<!--                                    <a href="#editing-field" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">-->
<!--                                        <span class="truncate">{{ settingsLabel }}</span>-->
<!--                                        <div class="grid *:[grid-area:1/1]">-->
<!--                                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />-->
<!--                                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />-->
<!--                                        </div>-->
<!--                                    </a>-->
<!--                                </div>-->

<!--                                <div class="space-y-4">-->
<!--                                    <LogicFlowMock :use-when-selector="true" />-->
<!--                                    <Button size="sm" variant="subtle" class="ms-4 bg-transparent!" :text="__('+ Add Condition')" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </TabContent>-->

<!--                        <TabContent name="validation">-->
<!--                            <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Validation') }}</p>-->
<!--                        </TabContent>-->
<!--                    </Tabs>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

        <!-- Desktop -->
        <div class="@container relative pt-6 pb-12 px-2.5 pe-4.5 max-[1000px]:hidden">
            <div class="space-y-6 pt-8">
                <div class="flex items-center gap-2">
                    <div class="size-4">
                        <Icon :name="fieldtype.icon" class="size-4 text-gray-500 dark:text-gray-300" />
                    </div>
                    <a class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                        <span class="truncate">{{ __(fieldtype.title) }}</span>
                    </a>
                </div>

                <p v-if="fieldtype.description" class="text-zinc-600">{{ __(fieldtype.description) }}</p>

                <div v-if="fieldtype.example">
                    <Badge class="mb-2" size="sm" :text="__('Example')" />
                    <div class="bg-white rounded-md p-4 border" inert>
                        <PublishContainer
                            :meta="{[fieldtype.handle]: fieldtype.example.meta}"
                            :blueprint="{tabs: [{ fields: fieldtype.example.config }]}"
                            :model-value="{[fieldtype.handle]: fieldtype.example.value}"
                        >
                            <PublishFieldsProvider :fields="[fieldtype.example.config]">
                                <PublishFields />
                            </PublishFieldsProvider>
                        </PublishContainer>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
