<script setup lang="ts">
import LogicFlowMock from '@/pages/forms/LogicFlowMock.vue';
import { Button, Tabs, TabList, TabTrigger, TabContent, PublishContainer, PublishFieldsProvider, PublishField, Icon } from '@ui';
import { ref, onMounted, watch } from 'vue';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';
import debounce from '@/util/debounce.js';
import axios from 'axios';

const cache = new Map<string, { fieldtype: any; blueprint: any; values: any; meta: any; originValues: any; originMeta: any }>();

const { form, inspecting: field } = injectBuilderContext();

enum FieldTabs {
    Settings = 'settings',
    Conditions = 'conditions',
    Validation = 'validation',
}

const loading = ref(true);
const error = ref(null);
const errors = ref({});
const values = ref(null);
const meta = ref(null);
const originValues = ref(null);
const originMeta = ref(null);
const fieldtype = ref(null);
const blueprint = ref(null);
const activeTab = ref<FieldTabs>(FieldTabs.Settings);

const load = () => {
    const cached = cache.get(field.value._id);

    if (cached) {
        loading.value = false;
        fieldtype.value = cached.fieldtype;
        blueprint.value = cached.blueprint;
        values.value = cached.values;
        meta.value = cached.meta;
        originValues.value = cached.originValues;
        originMeta.value = cached.originMeta;
        return;
    }

    loading.value = true;

    axios
        .post(cp_url(`forms/${form.handle}/builder/fields/edit`), {
            type: field.value.fieldtype,
            reference: field.value.type === 'reference' ? field.value.field_reference : false,
            values: field.value.config,
        })
        .then((response) => {
            loading.value = false;
            fieldtype.value = response.data.fieldtype;
            blueprint.value = response.data.blueprint;
            values.value = response.data.values;
            meta.value = response.data.meta;
            originValues.value = response.data.originValues;
            originMeta.value = response.data.originMeta;

            cache.set(field.value._id, {
                fieldtype: response.data.fieldtype,
                blueprint: response.data.blueprint,
                values: response.data.values,
                meta: response.data.meta,
                originValues: response.data.originValues,
                originMeta: response.data.originMeta,
            });
        })
        .catch((e) => {
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                error.value = message;
                errors.value = errors;
                Statamic.$toast.error(message);
            } else {
                Statamic.$toast.error(e.response?.data?.message || __('Something went wrong'));
            }
        });
};

const updatePreview = debounce(() => {
    const fieldId = field.value._id;

    axios
        .post(cp_url(`forms/${form.handle}/builder/fields/update`), {
            type: field.value.fieldtype,
            values: values.value,
        })
        .then((response) => {
            if (field.value._id !== fieldId) return;

            field.value.config = response.data.values;

            if (response.data.preview) {
                field.value.preview = {
                    config: { ...response.data.preview.config, handle: field.value.handle },
                    value: response.data.preview.value,
                    meta: response.data.preview.meta,
                };
            }

            cache.delete(field.value._id);
        });
}, 500);

watch(field, () => {
    updatePreview.cancel();
    loading.value = !cache.has(field.value._id);
    load();
});

watch(values, () => updatePreview(), { deep: true });

onMounted(() => load());
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
            <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
                <Icon name="loading" />
            </div>

            <Tabs v-else v-model:modelValue="activeTab" :unmount-on-hide="false">
                <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                    <TabTrigger :name="FieldTabs.Settings" :text="__('Settings')" />
                    <TabTrigger :name="FieldTabs.Conditions" :text="__('Logic')" />
                    <TabTrigger :name="FieldTabs.Validation" :text="__('Validation')" />
                </TabList>

                <TabContent :name="FieldTabs.Settings">
                    <div class="space-y-6 pt-8">
                        <div data-field-settings class="flex items-center gap-2">
                            <div class="size-4">
                                <Icon :name="field.icon" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="`#field-${field._id}`" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                                <span class="truncate">{{ field.config.display }}</span>
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <PublishContainer
                            ref="container"
                            :blueprint
                            :meta
                            :errors
                            v-model="values"
                            :origin-values
                            :origin-meta
                        >
                            <PublishFieldsProvider>
                                <div class="publish-fields">
                                    <PublishField
                                        v-for="field in blueprint.tabs[0].sections[0].fields"
                                        :key="field.handle"
                                        :config="field"
                                        :class="[
                                            'form-group',
                                            `field-w-${field.width}`
                                        ]"
                                    />
                                </div>
                            </PublishFieldsProvider>
                        </PublishContainer>
                    </div>
                </TabContent>

                <TabContent :name="FieldTabs.Conditions">
                    <div class="space-y-6 pt-8">
                        <div data-field-settings class="flex items-center gap-2">
                            <div class="size-4">
                                <Icon :name="field.icon" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="`#field-${field._id}`" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                                <span class="truncate">{{ field.config.display }}</span>
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <div class="space-y-4">
                            <LogicFlowMock :use-when-selector="true" />
                            <Button size="sm" variant="subtle" class="ms-4 bg-transparent!" :text="__('+ Add Condition')" />
                        </div>
                    </div>
                </TabContent>

                <TabContent :name="FieldTabs.Validation">
                    <div class="space-y-6 pt-8">
                        <div data-field-settings class="flex items-center gap-2">
                            <div class="size-4">
                                <Icon :name="field.icon" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="`#field-${field._id}`" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                                <span class="truncate">{{ field.config.display }}</span>
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>
                    </div>
                </TabContent>
            </Tabs>
        </div>
    </div>
</template>
