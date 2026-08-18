<script setup lang="ts">
import { Button, Field, Icon, Input, TabContent, TabList, Tabs, TabTrigger } from '@ui';
import { computed, ref, watch } from 'vue';
import PageRuleBuilder from './pages/PageRuleBuilder.vue';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';
import { __ } from '@/bootstrap/globals';

const { dirty, fieldtypes, inspecting: page, pages } = injectBuilderContext();

enum ActionInspectorTabs {
    Settings = 'settings',
    Logic = 'logic',
}

const activeTab = ref<ActionInspectorTabs>(ActionInspectorTabs.Settings);

const pageIndex = computed(() => pages.value.findIndex((p) => p._id === page.value._id));
const isFirstPage = computed(() => pageIndex.value === 0);
const isLastPage = computed(() => pageIndex.value === pages.value.length - 1);

const title = computed(() => isLastPage.value ? __('Submit button') : __('Page Buttons'));
const submitButtonLabel = computed(() => isLastPage.value ? __('Submit Button Label') : __('Next Button Label'));
const submitButtonPlaceholder = computed(() => isLastPage.value ? __('Submit') : __('Next Page'));

const suggestableConditionFields = computed(() => {
    return pages.value
        .slice(0, pageIndex.value + 1)
        .flatMap((page) => page.sections)
        .flatMap((section) => section.fields)
        .filter((f) => f.type === 'import' || f.handle)
        .map((f) => ({
            handle: f.handle,
            config: {
                ...f.config,
                type: f.fieldtype,
            },
            icon: f.icon,
        }));
});

const pageDestinationOptions = computed(() => {
    const currentPageIndex = pageIndex.value;

    return pages.value
        .slice(currentPageIndex + 1)
        .map((p, index) => {
            const actualIndex = currentPageIndex + 1 + index;
            const label = p.display
                ? __(p.display)
                : __('Page :current of :total', { current: actualIndex + 1, total: pages.value.length });

            return {
                value: p._id,
                label,
                icon: 'page',
            };
        });
});

watch(page, () => {
    if (activeTab.value === ActionInspectorTabs.Logic && isLastPage.value) {
        activeTab.value = ActionInspectorTabs.Settings;
    }
});

watch(() => page.value.button_label, dirty);
watch(() => page.value.show_previous_button, dirty);
watch(() => page.value.previous_page_label, dirty);
watch(() => page.value.rules, dirty, { deep: true });

const addPreviousButton = () => {
    page.value.show_previous_button = true;
};

const removePreviousButton = () => {
    page.value.show_previous_button = false;
    page.value.previous_page_label = null;
};
</script>

<template>
    <div class="@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5">
        <Tabs v-model:modelValue="activeTab" :unmount-on-hide="false">
            <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                <TabTrigger :name="ActionInspectorTabs.Settings" :text="__('Settings')" />
                <TabTrigger v-if="!isLastPage" :name="ActionInspectorTabs.Logic" :text="__('Logic')" />
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

                    <template v-if="!isFirstPage">
                        <Button
                            v-if="!page.show_previous_button"
                            size="sm"
                            :text="__('Add Previous Button')"
                            @click="addPreviousButton"
                        />

                        <Field v-else :label="__('Previous Button Label')">
                            <template #actions>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    :aria-label="__('Remove Previous Button')"
                                    @click="removePreviousButton"
                                />
                            </template>
                            <Input v-model="page.previous_page_label" :placeholder="__('Previous Page')" />
                        </Field>
                    </template>
                </div>
            </TabContent>

            <TabContent v-if="!isLastPage" :name="ActionInspectorTabs.Logic">
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

                    <PageRuleBuilder
                        v-model:rules="page.rules"
                        :suggestable-fields="suggestableConditionFields"
                        :page-destination-options
                        :fieldtypes
                    />
                </div>
            </TabContent>
        </Tabs>
    </div>
</template>
