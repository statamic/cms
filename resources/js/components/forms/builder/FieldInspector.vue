<script setup lang="ts">
import { Button, Icon, PublishContainer, PublishFields, PublishFieldsProvider, TabContent, TabList, Tabs, TabTrigger } from '@ui';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';
import { snake_case } from '@/bootstrap/globals';
import FieldValidationBuilder from '@/components/field-validation/Builder.vue';
import FieldConditionsBuilder from '@/components/field-conditions/Builder.vue';
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { categories, categoryColorClasses, collectsValue } from './categories';
import { LOGIC_KEYS, writeFieldConditions } from '@/composables/forms/field-conditions';
import { addConfigOverrides, removeConfigOverrides } from '@/composables/forms/config-overrides';
import { expandImportedFields } from '@/composables/forms/suggestable-fields';
import { KEYS as FIELD_CONDITIONS_KEYS } from '@/components/field-conditions/Constants.js';
import debounce from '@/util/debounce';

const cache = new Map<string, { fieldtype: any; blueprint: any; values: any; meta: any; originValues: any; originMeta: any }>();

const latestUpdates = new WeakMap<object, number>();
let updateCount = 0;

const { dirty, errors: contextErrors, fieldtypes, form, inspecting: field, onBeforeSave, pages, showFieldDirection, withoutDirtying } = injectBuilderContext();

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

let skipNextPreviewUpdate = false;
let valuesField = null;
let previousValues = {};
let pendingLoad: Promise<void> | null = null;
let pendingUpdate: { field: any; values: any } | null = null;
const updatesInFlight = new Set<Promise<void>>();

const extraValues = computed(() => ({
    isNew: !!field.value.isNew,
}));

const shouldShowValidationTab = computed(() => collectsValue(findFieldtype(field.value.config.type)));

const adjustedBlueprint = computed(() => {
    const bp = JSON.parse(JSON.stringify(blueprint.value));

    bp.tabs[0].sections.forEach((section) => {
        section.fields.forEach((field) => {
            field.localizable = true;
        });
    });

    return bp;
});

const clone = (value) => JSON.parse(JSON.stringify(value ?? null));

const setValues = (target, newValues) => {
    valuesField = target;
    previousValues = clone(newValues);
    skipNextPreviewUpdate = true;
    withoutDirtying(() => (values.value = newValues));
};

const load = () => {
    valuesField = null;
    pendingLoad = null;

    const target = field.value;
    const cached = cache.get(target._id);

    if (cached) {
        loading.value = false;
        fieldtype.value = cached.fieldtype;
        blueprint.value = cached.blueprint;
        setValues(target, cached.values);
        meta.value = cached.meta;
        originValues.value = cached.originValues;
        originMeta.value = cached.originMeta;
        return;
    }

    loading.value = true;

    pendingLoad = axios
        .post(cp_url(`forms/${form.handle}/builder/fields/edit`), {
            type: field.value.fieldtype,
            reference: field.value.type === 'reference' ? field.value.field_reference : false,
            values: {
                ...field.value.config,
                handle: field.value.handle,
            },
        })
        .then((response) => {
            if (field.value !== target) {
                if (target.isNew && !target.handle && target.config?.display) target.handle = snake_case(target.config.display);
                return;
            }

            loading.value = false;
            fieldtype.value = response.data.fieldtype;
            blueprint.value = response.data.blueprint;
            setValues(target, response.data.values);
            meta.value = response.data.meta;
            originValues.value = response.data.originValues;
            originMeta.value = response.data.originMeta;

            cache.set(target._id, {
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

const sendPendingUpdate = () => {
    if (!pendingUpdate) return;

    const { field: target, values: payload } = pendingUpdate;
    const id = ++updateCount;
    pendingUpdate = null;
    latestUpdates.set(target, id);

    const request = axios
        .post(cp_url(`forms/${form.handle}/builder/fields/update`), {
            type: target.fieldtype,
            values: payload,
        })
        .then((response) => {
            if (latestUpdates.get(target) !== id || pendingUpdate?.field === target) return;

            const { handle, ...config } = response.data.values;

            target.config = config;

            if (response.data.preview) {
                target.preview = {
                    config: {
                        ...response.data.preview.config,
                        handle: target.handle ?? target._id,
                    },
                    value: response.data.preview.value,
                    meta: response.data.preview.meta,
                };
            }

            cache.delete(target._id);
        })
        .finally(() => updatesInFlight.delete(request));

    updatesInFlight.add(request);
};

const updatePreview = debounce(sendPendingUpdate, 500);

const flushUpdates = () => {
    updatePreview.cancel();
    sendPendingUpdate();
};

const flush = async () => {
    await pendingLoad?.catch(() => {});
    await nextTick();
    flushUpdates();
    await Promise.allSettled([...updatesInFlight]);
};

const currentPageIndex = computed(() => {
    return pages.value.findIndex((page) =>
        page.sections.some((section) => section.fields.some((f) => f._id === field.value._id))
    );
});

const fieldsets = usePage().props.fieldsets;

const suggestableConditionFields = computed(() => {
    const fields = pages.value
        .slice(0, currentPageIndex.value + 1)
        .flatMap((page) => page.sections)
        .flatMap((section) => section.fields)
        .filter((f) => f._id !== field.value._id);

    return expandImportedFields(fields, fieldsets)
        .filter((f) => f.handle)
        .filter((f) => collectsValue(findFieldtype(f.config.type)))
        .map((f) => ({
            handle: f.handle,
            config: {
                ...f.config,
                type: f.fieldtype,
            },
            icon: f.icon,
        }));
});

const findFieldtype = (fieldtypeHandle: string) => fieldtypes?.find((field) => field.handle === fieldtypeHandle);

const getFieldtypeCategoryHandle = (fieldtypeHandle: string) => findFieldtype(fieldtypeHandle)?.categories?.[0] || 'other';

const getFieldtypeCategory = (fieldtypeHandle: string) => categories[getFieldtypeCategoryHandle(fieldtypeHandle)] ?? categories.other;

const fieldIconClasses = (fieldtypeHandle: string) => `size-4 shrink-0 ${categoryColorClasses[getFieldtypeCategory(fieldtypeHandle)?.color]?.icon}`;
const findSuggestableField = (handle: string) => suggestableConditionFields.value.find((f) => f.handle === handle);

const updateFieldConditions = (conditions: Record<string, any>) => {
    writeFieldConditions(field.value, conditions);
    FIELD_CONDITIONS_KEYS.forEach((key) => delete values.value[key]);
    Object.assign(values.value, conditions);
    dirty();
};

watch(field, () => {
    flushUpdates();

    if (!field.value) return;

    loading.value = !cache.has(field.value._id);
    load();

    if (activeTab.value === FieldInspectorTabs.Validation && !shouldShowValidationTab.value) {
        activeTab.value = FieldInspectorTabs.Settings;
    }
});

const changedKeys = (before, after) => {
    const keys = new Set([...Object.keys(before ?? {}), ...Object.keys(after ?? {})]);

    return [...keys].filter((key) => JSON.stringify(before?.[key]) !== JSON.stringify(after?.[key]));
};

const differsFromOrigin = (key) => !originValues.value || !(key in originValues.value)
    || JSON.stringify(originValues.value[key]) !== JSON.stringify(values.value[key]);

watch(values, () => {
    dirty();

    const changed = changedKeys(previousValues, values.value);
    previousValues = clone(values.value);

    if (skipNextPreviewUpdate) {
        skipNextPreviewUpdate = false;
        return;
    }

    if (!valuesField) return;

    const settings = changed.filter((key) => key !== 'handle' && !LOGIC_KEYS.includes(key));

    addConfigOverrides(valuesField, settings.filter((key) => differsFromOrigin(key)));
    removeConfigOverrides(valuesField, settings.filter((key) => !differsFromOrigin(key)));

    pendingUpdate = { field: valuesField, values: clone(values.value) };
    updatePreview();
}, { deep: true });

watch(() => field.value?.config?.width, (width) => {
    if (!values.value || valuesField !== field.value) return;
    if ((values.value.width ?? 100) !== (width ?? 100)) values.value.width = width ?? 100;
});

watch(() => values.value?.handle, (handle: string) => {
    if (field.value.isNew && handle !== undefined) field.value.handle = handle;
});

let removeBeforeSave = null;

onMounted(() => {
    removeBeforeSave = onBeforeSave(flush);
    load();
});

onBeforeUnmount(() => {
    removeBeforeSave?.();
    flushUpdates();

    if (updatesInFlight.size === 0) return;

    const settled = Promise.allSettled([...updatesInFlight]);
    const removeUntilSettled = onBeforeSave(() => settled);
    settled.then(() => removeUntilSettled());
});
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
                            <span class="truncate">{{ __(field.config.display) }}</span>
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
                        :extra-values
                        v-model="values"
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
                            <span class="truncate">{{ __(field.config.display) }}</span>
                            <div v-if="showFieldDirection" class="grid *:[grid-area:1/1]">
                                <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                            </div>
                        </a>
                    </div>

                    <FieldConditionsBuilder
                        :key="field._id"
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
                            <span class="truncate">{{ __(field.config.display) }}</span>
                            <div v-if="showFieldDirection" class="grid *:[grid-area:1/1]">
                                <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                            </div>
                        </a>
                    </div>

                    <FieldValidationBuilder :key="field._id" :config="values" :show-unique-value-rules="false" @updated="values.validate = $event" />
                </div>
            </TabContent>
        </Tabs>
    </div>
</template>
