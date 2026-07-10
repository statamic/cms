<script setup lang="ts">
import { Button, Icon, PublishContainer, PublishFields, PublishFieldsProvider, TabContent, TabList, Tabs, TabTrigger } from '@ui';
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';
import FieldValidationBuilder from '@/components/field-validation/Builder.vue';
import FieldConditionsBuilder from '@/components/field-conditions/Builder.vue';
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { categories, categoryColorClasses } from './categories';
import debounce from '@/util/debounce';

const cache = new Map<string, { fieldtype: any; blueprint: any; values: any; meta: any; originValues: any; originMeta: any }>();

const { dirty, errors: contextErrors, fieldtypes, form, inspecting: field, pages, showFieldDirection, withoutDirtying } = injectBuilderContext();

const errors = computed(() => {
    const result = {};
    const prefix = `${field.value._id}.`;
    const allErrors = contextErrors.value ?? {};

    for (const [key, messages] of Object.entries(allErrors)) {
        if (key.startsWith(prefix)) {
            const configField = key.slice(prefix.length);
            result[configField] = messages;
        }
    }

    return result;
});

enum FieldInspectorTabs {
    Settings = 'settings',
    Logic = 'logic',
    Validation = 'validation',
}

const loading = ref(true);
const values = ref(null);
const meta = ref(null);
const originValues = ref(null);
const originMeta = ref(null);
const fieldtype = ref(null);
const blueprint = ref(null);
const activeTab = ref<FieldInspectorTabs>(FieldInspectorTabs.Settings);
const modifiedFields = ref<string[]>([]);

const shouldShowValidationTab = computed(() => !['structure', 'information'].includes(getFieldtypeCategory(field.value.config.type).handle));

const adjustedBlueprint = computed(() => {
    const bp = JSON.parse(JSON.stringify(blueprint.value));

    bp.tabs[0].sections.forEach((section) => {
        section.fields.forEach((field) => {
            field.localizable = true;
        });
    });

    return bp;
});

const load = () => {
    modifiedFields.value = field.value.config_overrides ?? [];

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
            withoutDirtying(() => (values.value = response.data.values));
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
                const { message } = e.response.data;
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

const currentPageIndex = computed(() => {
    return pages.value.findIndex((page) =>
        page.sections.some((section) => section.fields.some((f) => f._id === field.value._id))
    );
});

const suggestableConditionFields = computed(() => {
    return pages.value
        .slice(0, currentPageIndex.value + 1)
        .flatMap((page) => page.sections)
        .flatMap((section) => section.fields)
        .filter((f) => f._id !== field.value._id)
        .filter((f) => f.type === 'import' || !['structure', 'information'].includes(getFieldtypeCategory(f.config.type).handle))
        .map((f) => ({
            handle: f.handle,
            config: {
                ...f.config,
                type: f.fieldtype,
            },
            icon: f.icon,
        }));
});

const getFieldtypeCategory = (fieldtypeHandle: string) => {
    const fieldtype = fieldtypes?.find((field) => field.handle === fieldtypeHandle);
    const categoryKey = fieldtype?.categories?.[0] || 'other';
    return categories[categoryKey] ?? categories.other;
};

const fieldIconClasses = (fieldtypeHandle: string) => `size-4 shrink-0 ${categoryColorClasses[getFieldtypeCategory(fieldtypeHandle)?.color]?.icon}`;
const findSuggestableField = (handle: string) => suggestableConditionFields.value.find((f) => f.handle === handle);

const updateFieldConditions = (conditions: Record<string, any>) => {
    Object.assign(field.value.config, conditions);
    Object.assign(values.value, conditions);
    dirty();
};

watch(field, () => {
    updatePreview.cancel();
    loading.value = !cache.has(field.value._id);
    load();

    if (activeTab.value === FieldInspectorTabs.Validation && !shouldShowValidationTab.value) {
        activeTab.value = FieldInspectorTabs.Settings;
    }
});

watch(values, () => {
    dirty();
    updatePreview();
}, { deep: true });

watch(modifiedFields, (fields) => {
    if (field.value.type === 'reference') {
        field.value.config_overrides = fields;
    }
}, { deep: true });

onMounted(() => load());
</script>

<template>
    <div class="@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5">
        <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <Icon name="loading" />
        </div>

        <Tabs v-else v-model:modelValue="activeTab" :unmount-on-hide="false">
            <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                <TabTrigger :name="FieldInspectorTabs.Settings" :text="__('Settings')" />
                <TabTrigger :name="FieldInspectorTabs.Logic" :text="__('Logic')" />
                <TabTrigger v-if="shouldShowValidationTab" :name="FieldInspectorTabs.Validation" :text="__('Validation')" />
            </TabList>

            <TabContent :name="FieldInspectorTabs.Settings">
                <div class="space-y-6 pt-8">
                    <div data-field-settings class="flex items-center gap-2.5">
                        <div class="size-4">
                            <Icon :name="field.icon" class="size-4 text-gray-500 dark:text-gray-300" />
                        </div>
                        <a :href="`#field-${field._id}`" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                            <span class="truncate">{{ field.config.display }}</span>
                            <div v-if="showFieldDirection" class="grid *:[grid-area:1/1]">
                                <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                            </div>
                        </a>
                    </div>

                    <PublishContainer
                        ref="container"
                        :blueprint="adjustedBlueprint"
                        :meta
                        :errors
                        v-model="values"
                        v-model:modified-fields="modifiedFields"
                        :origin-values
                        :origin-meta
                        :track-dirty-state="false"
                    >
                        <PublishFieldsProvider :fields="adjustedBlueprint.tabs[0].sections[0].fields">
                            <PublishFields />
                        </PublishFieldsProvider>
                    </PublishContainer>
                </div>
            </TabContent>

            <TabContent :name="FieldInspectorTabs.Logic">
                <div class="space-y-6 pt-8">
                    <div class="flex items-center gap-2.5">
                        <div class="size-4">
                            <Icon :name="field.icon" class="size-4 text-gray-500 dark:text-gray-300" />
                        </div>
                        <a :href="`#field-${field._id}`" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                            <span class="truncate">{{ field.config.display }}</span>
                            <div v-if="showFieldDirection" class="grid *:[grid-area:1/1]">
                                <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                            </div>
                        </a>
                    </div>

                    <FieldConditionsBuilder
                        :config="field.config"
                        :suggestable-fields="suggestableConditionFields"
                        :allow-custom-conditions="false"
                        :show-always-hide-option="true"
                        size="sm"
                        @updated="updateFieldConditions"
                    >
                        <template #field-option="{ value, label }">
                            <span class="inline-flex items-center gap-2">
                                <FieldNumber :field-key="value" />
                                <Icon
                                    v-if="findSuggestableField(value)?.icon"
                                    :name="findSuggestableField(value).icon"
                                    :class="fieldIconClasses(findSuggestableField(value)?.config?.type)"
                                />
                                <span class="truncate">{{ __(label) }}</span>
                            </span>
                        </template>
                        <template #field-selected="{ option, field: selectedField }">
                            <span class="inline-flex items-center gap-2 truncate">
                                <FieldNumber :field-key="option.value" />
                                <Icon
                                    v-if="selectedField?.icon"
                                    :name="selectedField.icon"
                                    :class="fieldIconClasses(selectedField.config.type)"
                                />
                                <span class="truncate">{{ __(findSuggestableField(option.value)?.config?.display ?? option.value) }}</span>
                            </span>
                        </template>
                    </FieldConditionsBuilder>
                </div>
            </TabContent>

            <TabContent :name="FieldInspectorTabs.Validation">
                <div class="space-y-6 pt-8">
                    <div data-field-settings class="flex items-center gap-2.5">
                        <div class="size-4">
                            <Icon :name="field.icon" class="size-4 text-gray-500 dark:text-gray-300" />
                        </div>
                        <a :href="`#field-${field._id}`" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                            <span class="truncate">{{ field.config.display }}</span>
                            <div v-if="showFieldDirection" class="grid *:[grid-area:1/1]">
                                <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                            </div>
                        </a>
                    </div>

                    <FieldValidationBuilder :config="values" @updated="values.validate = $event" />
                </div>
            </TabContent>
        </Tabs>
    </div>
</template>
