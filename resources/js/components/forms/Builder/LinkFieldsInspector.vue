<script setup lang="ts">
import { Field, Icon, Input, Combobox, RadioGroup, Radio, Button } from '@ui';
import { computed, ref, watch } from 'vue';
import { injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import { __ } from '@/bootstrap/globals';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const { form, inspecting: fieldsetImport, inspect, dirty } = injectBuilderContext();

const fieldset = ref<string>(null);
const reference = ref<string>(null);
const importPrefix = ref<string>(null);
const sectionBehavior = ref<string>('preserve');
const loadingPreviews = ref<boolean>(false);
const fieldsetPreviews = ref<Record<string, any>>({});

const fieldsets = Object.values(usePage().props.fieldsets);

const fieldSuggestions = fieldsets.flatMap((fieldset) => {
    return fieldset.fields
        .filter((field) => field.type !== 'import')
        .map((field) => ({
            value: `${fieldset.handle}.${field.handle}`,
            label: __(field.config.display),
            fieldset: __(fieldset.title),
        }));
});

const fieldsetSuggestions = computed(() =>
    fieldsets.map((fieldset) => ({
        value: fieldset.handle,
        label: __(fieldset.title),
    })));

const selectedFieldsetHasSections = computed(() => {
    if (!fieldset.value) return false;

    return fieldsets.find((f) => f.handle === fieldset.value)?.has_sections === true;
});

const loadFieldsetPreviews = () => {
    if (!fieldset.value) return;

    loadingPreviews.value = true;

    axios
        .post(cp_url(`forms/${form.handle}/builder/fieldset-previews`), { fieldset: fieldset.value })
        .then((response) => (fieldsetPreviews.value = response.data.previews))
        .catch(() => (fieldsetPreviews.value = {}))
        .finally(() => (loadingPreviews.value = false));
};

const linkField = () => {
    const lastDot = reference.value.lastIndexOf('.');
    const fieldsetHandle = reference.value.substring(0, lastDot);
    const fieldHandle = reference.value.substring(lastDot + 1);

    const fieldsetData = fieldsets.find((f) => f.handle === fieldsetHandle);
    const fieldData = fieldsetData.fields.find((f) => f.handle === fieldHandle);

    Object.assign(fieldsetImport.value, {
        handle: fieldHandle,
        type: 'reference',
        field_reference: reference.value,
        config: { ...fieldData.config },
        config_overrides: [],
        fieldtype: fieldData.config.type,
        icon: fieldData.icon,
        preview: null,
    });

    dirty();

    inspect(InspectorType.Field, fieldsetImport.value);
};

const linkFieldset = () => {
    Object.assign(fieldsetImport.value, {
        type: 'import',
        fieldset: fieldset.value,
        prefix: importPrefix.value || null,
        previews: { ...fieldsetPreviews.value },
    });

    if (selectedFieldsetHasSections.value && sectionBehavior.value !== 'preserve') {
        fieldsetImport.value.section_behavior = sectionBehavior.value;
    }

    dirty();

    inspect(InspectorType.FieldsetImport, fieldsetImport.value);
};

watch(fieldset, () => {
    if (!selectedFieldsetHasSections.value) {
        sectionBehavior.value = 'preserve';
    }

    loadFieldsetPreviews();
});
</script>

<template>
    <div>
        <!-- Mobile -->
<!--        <div class="right-panel-popover min-[1000px]:hidden">-->
<!--            <div id="popover-right-panel" class="right-panel-popover__menu" popover>-->
<!--                <button class="right-panel-popover__close-button" :title="__('Close')" popovertarget="popover-right-panel">-->
<!--                    <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>-->
<!--                </button>-->
<!--                <div class="@container pt-6 pb-40 px-2.5">-->
<!--                    <Tabs v-model:modelValue="activeTab" :unmount-on-hide="false">-->
<!--                        <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">-->
<!--                            <TabTrigger :name="ActionInspectorTabs.Settings" :text="__('Settings')" />-->
<!--                        </TabList>-->

<!--                        <TabContent :name="ActionInspectorTabs.Settings">-->
<!--                            <div class="space-y-6 pt-8">-->
<!--                                <div class="flex items-center gap-2.5">-->
<!--                                    <div class="size-4">-->
<!--                                        <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />-->
<!--                                    </div>-->
<!--                                    <a :href="`#actions-${page._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">-->
<!--                                        {{ title }}-->
<!--                                        <div class="grid *:[grid-area:1/1]">-->
<!--                                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />-->
<!--                                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />-->
<!--                                        </div>-->
<!--                                    </a>-->
<!--                                </div>-->

<!--                                <Field :label="submitButtonLabel">-->
<!--                                    <Input v-model="page.button_label" :placeholder="submitButtonPlaceholder" />-->
<!--                                </Field>-->

<!--                                <Field v-if="!isFirstPage" :label="__('Previous Button Label')">-->
<!--                                    <Input v-model="page.previous_page_label"  :placeholder="__('Previous Page')" />-->
<!--                                </Field>-->
<!--                            </div>-->
<!--                        </TabContent>-->
<!--                    </Tabs>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

        <!-- Desktop -->
        <div class="@container relative pt-6 pb-12 px-2.5 pe-4.5 max-[1000px]:hidden">
            <div class="space-y-6 pt-8">
                <div class="flex items-center gap-2.5">
                    <div class="size-4">
                        <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                    </div>
                    <a :href="`#fieldset-${fieldsetImport._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                        {{ __('Link Fields') }}
                        <div class="grid *:[grid-area:1/1]">
                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                        </div>
                    </a>
                </div>

                <Field
                    :label="__('Link a single field')"
                    :instructions="__('Changes to this field in the fieldset will stay in sync.')"
                >
                    <Combobox
                        :placeholder="__('Fields')"
                        :options="fieldSuggestions"
                        searchable
                        :model-value="reference"
                        @update:modelValue="reference = $event"
                    >
                        <template #option="option">
                            <div class="flex items-center">
                                    <span
                                        v-text="option.fieldset"
                                        class="text-2xs text-gray-500 dark:text-gray-300 ltr:mr-2 rtl:ml-2"
                                    />
                                <span v-text="option.label" />
                            </div>
                        </template>
                        <template #no-options>
                            <div
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-500 ltr:text-left rtl:text-right"
                                v-text="__('No options to choose from.')"
                            />
                        </template>
                    </Combobox>
                </Field>

                <Button
                    class="w-full mt-6"
                    variant="primary"
                    :disabled="!reference"
                    :text="__('Link Field')"
                    @click="linkField"
                />

                <div class="my-4 flex items-center">
                    <div class="flex-1 border-b border-gray-300 dark:border-gray-500" />
                    <div class="mx-4 text-2xs text-gray-600 dark:text-gray-400" v-text="__('or')"></div>
                    <div class="flex-1 border-b border-gray-300 dark:border-gray-500" />
                </div>

                <Field
                    class="mb-6"
                    :label="__('Link a fieldset')"
                    :instructions="__('Changes to this fieldset will stay in sync.')"
                >
                    <Combobox
                        :placeholder="__('Fieldsets')"
                        :options="fieldsetSuggestions"
                        searchable
                        :model-value="fieldset"
                        @update:modelValue="fieldset = $event"
                    >
                        <template #no-options>
                            <div
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-500 ltr:text-left rtl:text-right"
                                v-text="__('No options to choose from.')"
                            />
                        </template>
                    </Combobox>
                </Field>

                <Field
                    :label="__('Prefix')"
                    :instructions="__('messages.fieldset_link_fields_prefix_instructions')"
                >
                    <Input v-model="importPrefix" :placeholder="__('e.g. hero_')" />
                </Field>

                <Field
                    v-if="selectedFieldsetHasSections"
                    :label="__('Section Behavior')"
                    :instructions="__('messages.fieldset_import_section_behavior_instructions')"
                    class="mt-6"
                >
                    <RadioGroup v-model="sectionBehavior">
                        <Radio :label="__('Preserve')" :description="__('Keep imported sections as-is.')" value="preserve" />
                        <Radio :label="__('Flatten')" :description="__('Merge all fields into this section.')" value="flatten" />
                    </RadioGroup>
                </Field>

                <Button
                    class="w-full mt-6"
                    variant="primary"
                    :disabled="!fieldset"
                    :text="__('Link Fieldset')"
                    @click="linkFieldset"
                />
            </div>
        </div>
    </div>
</template>
