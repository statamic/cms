<script setup lang="ts">
import { Button, ConfirmationModal, Field, Icon, Input, TabContent, TabList, Tabs, TabTrigger, Textarea } from '@ui';
import { computed, ref, watch } from 'vue';
import PageRuleBuilder from './pages/PageRuleBuilder.vue';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';

const { deletePage, dirty, fieldtypes, inspecting: page, pages, showFieldDirection } = injectBuilderContext();

enum PageInspectorTabs {
    Settings = 'settings',
    Logic = 'logic',
}

const confirmingDelete = ref(false);
const activeTab = ref<PageInspectorTabs>(PageInspectorTabs.Settings);

const pageIndex = computed(() => pages.value.findIndex((p) => p._id === page.value._id));
const isLastPage = computed(() => pageIndex.value === pages.value.length - 1);
const canDeletePage = computed(() => pages.value.length > 1);
const pageTitle = computed(() => page.value.display ? __(page.value.display) : placeholderTitle.value);
const placeholderTitle = computed(() => __('Page :current of :total', {
    current: pageIndex.value + 1,
    total: pages.value.length,
}));

const confirmDelete = () => confirmingDelete.value = true;
const handleDeletePage = () => deletePage(page.value._id);

const suggestableConditionFields = computed(() => {
    const currentPageIndex = pageIndex.value;

    return pages.value
        .slice(0, currentPageIndex + 1)
        .flatMap((page) => page.sections)
        .flatMap((section) => section.fields)
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
    if (activeTab.value === PageInspectorTabs.Logic && isLastPage.value) {
        activeTab.value = PageInspectorTabs.Settings;
    }
});

watch(() => page.value.display, dirty);
watch(() => page.value.instructions, dirty);
watch(() => page.value.rules, dirty, { deep: true });
</script>

<template>
    <div class="@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5">
        <Tabs v-model:modelValue="activeTab" :unmount-on-hide="false">
            <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                <TabTrigger :name="PageInspectorTabs.Settings" :text="__('Settings')" />
                <TabTrigger v-if="!isLastPage" :name="PageInspectorTabs.Logic" :text="__('Logic')" />
            </TabList>

            <TabContent :name="PageInspectorTabs.Settings">
                <div class="group/logic-tab space-y-6 pt-8">
                    <div class="flex items-center gap-2.5">
                        <div class="size-4">
                            <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                        </div>
                        <a :href="`#page-${page._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                            {{ pageTitle }}
                            <div v-if="showFieldDirection" class="grid *:[grid-area:1/1]">
                                <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                            </div>
                        </a>
                    </div>

                    <Field :label="__('Label')">
                        <Input v-model="page.display" :placeholder="placeholderTitle" focus />
                    </Field>

                    <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                        <Textarea v-model="page.instructions" :rows="2" resize="vertical" />
                    </Field>
                    <Button
                        size="sm"
                        icon="trash"
                        :text="__('Delete Page')"
                        @click="confirmDelete"
                    />
                </div>
            </TabContent>

            <TabContent v-if="!isLastPage" :name="PageInspectorTabs.Logic">
                <div class="space-y-6 pt-8">
                    <div class="flex items-center gap-2.5">
                        <div class="size-4">
                            <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                        </div>
                        <a :href="`#page-${page._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                            {{ pageTitle }}
                            <div v-if="showFieldDirection" class="grid *:[grid-area:1/1]">
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

        <ConfirmationModal
            v-model:open="confirmingDelete"
            :title="__('Delete Page')"
            :body-text="__('Are you sure you want to delete this page? All sections and fields in this page will also be deleted.')"
            :button-text="__('Delete')"
            danger
            @confirm="handleDeletePage"
        />
    </div>
</template>
