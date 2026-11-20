<script setup lang="ts">
import { Button, Combobox, Field, Icon, Input, Radio, RadioGroup } from '@ui';
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import { __ } from '@/bootstrap/globals';

const { dirty, form, inspect, inspecting: fieldsetImport } = injectBuilderContext();

const fieldset = ref<string | null>(null);
const reference = ref<string | null>(null);
const importPrefix = ref<string | null>(null);
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
    <div class="@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5">
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
</template>
