<script setup>
import { Button, Combobox, Field, Icon, Input, Switch, Text } from '@ui';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { nanoid as uniqid } from 'nanoid';
import Condition from './Condition.vue';
import Converter from './Converter.js';
import { KEYS } from './Constants.js';

const emit = defineEmits(['updated', 'updated-always-save', 'updated-reserve-space-when-hidden']);

const props = defineProps({
    config: { type: Object, required: true },
    suggestableFields: { type: Array, required: true },
    allowCustomConditions: { type: Boolean, default: true },
    showAlwaysHideOption: { type: Boolean, default: false },
    showAlwaysSave: { type: Boolean, default: true },
    size: { type: String, default: 'base' },
});

const initialized = ref(false);
const when = ref('always');
const type = ref('all');
const customMethod = ref(null);
const conditions = ref([]);
const alwaysSave = ref(false);
const reserveSpaceWhenHidden = ref(false);

const whenOptions = computed(() => {
    return [
        { label: __('Always show'), value: 'always', icon: 'eye' },
        props.showAlwaysHideOption ? { label: __('Always hide'), value: 'always_hide', icon: 'eye-closed' } : null,
        { label: __('Show when'), value: 'if', icon: 'eye' },
        { label: __('Hide when'), value: 'unless', icon: 'eye-closed' },
    ].filter(Boolean);
});

const joinOptions = [
    { label: __('All of the conditions pass'), short_label: __('And'), value: 'all' },
    { label: __('Any of the conditions pass'), short_label: __('Or'), value: 'any' },
    // { label: __('Custom method'), short_label: __('Custom'), value: 'custom' },
];

const isCustom = computed(() => type.value === 'custom');
const hasConditions = computed(() => when.value !== 'always' && when.value !== 'always_hide');

const add = () => {
    conditions.value.push({
        _id: uniqid(),
        field: null,
        operator: 'equals',
        value: null,
    });
};

const remove = (index) => conditions.value.splice(index, 1);

const toggleCustom = () => type.value = isCustom.value ? 'all' : 'custom';

const saveableConditions = computed(() => {
    const result = Object.fromEntries(KEYS.map((key) => [key, null]));
    if (!hasConditions.value) return result;

    const key = type.value === 'any' ? `${when.value}_any` : when.value;
    const filtered = conditions.value.filter((c) => c.field && c.value);
    const prepared = new Converter().toBlueprint(filtered);

    if (!isCustom.value && Object.keys(prepared).length) {
        result[key] = prepared;
    } else if (isCustom.value && customMethod.value) {
        result[key] = customMethod.value;
    }

    return result;
});

const prepareEditableOperator = (operator) => {
    switch (operator) {
        case 'is':
        case '==':
            return '';
        case 'isnt':
        case '!=':
            return 'not';
    }
    return operator;
};

const getInitialWhenState = () => {
    if (props.showAlwaysHideOption) {
        when.value = props.config.hidden ? 'always_hide' : 'always';
    }
};

const getInitialConditions = () => {
    const key = KEYS.find((k) => props.config[k]);
    const configConditions = key ? props.config[key] : null;

    if (!configConditions) return;

    when.value = key?.startsWith('unless') || key?.startsWith('hide_when') ? 'unless' : 'if';
    type.value = key?.endsWith('_any') ? 'any' : 'all';

    if (typeof configConditions === 'string') {
        type.value = 'custom';
        customMethod.value = configConditions;
        return;
    }

    conditions.value = new Converter().fromBlueprint(configConditions).map((condition) => ({
        ...condition,
        _id: uniqid(),
        operator: prepareEditableOperator(condition.operator),
    }));
};

const getInitialAlwaysSaveState = () => alwaysSave.value = props.config?.always_save ?? false;

const getInitialReserveSpaceWhenHiddenState = () => reserveSpaceWhenHidden.value = props.config?.reserve_space_when_hidden ?? false;

watch(() => props.config.hidden, (hidden) => {
    if (hidden) {
        when.value = 'always_hide';
    } else if (when.value === 'always_hide') {
        when.value = 'always';
    }
});

watch(when, (value) => {
    if (initialized.value) emit('updated', { hidden: value === 'always_hide' });
});

watch(saveableConditions, (conditions) => {
    if (initialized.value) emit('updated', conditions);
}, { deep: true });

watch(alwaysSave, (value) => {
    if (initialized.value) emit('updated-always-save', value);
});

watch(reserveSpaceWhenHidden, (value) => {
    if (initialized.value) emit('updated-reserve-space-when-hidden', value);
});

onMounted(() => {
    getInitialWhenState();
    getInitialConditions();
    getInitialAlwaysSaveState();
    getInitialReserveSpaceWhenHiddenState();
    if (conditions.value.length === 0) add();
    nextTick(() => initialized.value = true);
});
</script>

<template>
    <div class="w-full @container">
        <div data-logic-text class="logic-text group/rule">
            <ol>
                <li v-for="(condition, index) in conditions" :key="condition._id">
                    <template v-if="index === 0">
                        <div class="flex items-center" :class="{ 'logic-text__condition': hasConditions }">
                            <Combobox
                                v-model="when"
                                class="min-w-34 w-auto"
                                :options="whenOptions"
                                option-label="label"
                                option-value="value"
                                :searchable="false"
                                :size
                            >
                                <template #option="{ icon, label }">
                                    <span class="inline-flex items-center gap-2">
                                        <Icon v-if="icon" :name="icon" class="size-3.5 text-gray-700 dark:text-gray-200" />
                                        <span>{{ label }}</span>
                                    </span>
                                </template>
                                <template #selected-option="{ option }">
                                    <span class="inline-flex items-center gap-2">
                                        <Icon v-if="option.icon" :name="option.icon" class="size-3.5 text-gray-700 dark:text-gray-200" />
                                        <span>{{ option.label }}</span>
                                    </span>
                                </template>
                            </Combobox>

                            <Switch v-if="allowCustomConditions" class="ms-4" size="sm" :model-value="isCustom" @update:model-value="toggleCustom" />
                            <Text v-if="allowCustomConditions" class="ms-2" :text="__('Custom method passes')" />
                        </div>
                    </template>

                    <template v-else-if="!isCustom">
                        <div class="flex items-center gap-0.25">
                            <div class="logic-text__condition">
                                <Combobox
                                    v-model="type"
                                    class="max-w-24"
                                    :size
                                    :options="joinOptions"
                                    option-label="label"
                                    option-value="value"
                                    :placeholder="__('And')"
                                    :searchable="false"
                                    adaptive-width
                                >
                                    <template #selected-option="{ option }">
                                        <span class="block truncate">{{ option.short_label }}</span>
                                    </template>
                                </Combobox>
                            </div>
                            <div class="ms-0.5 mb-2.5 inline-flex items-center gap-1.5 opacity-0 pointer-events-none transition-opacity group-hover/rule:opacity-100 group-hover/rule:pointer-events-auto [@media(any-hover:none)]:opacity-100 [@media(any-hover:none)]:pointer-events-auto">
                                <Button
                                    :size
                                    inset
                                    variant="subtle"
                                    class="size-7 rounded-full [&_div]:opacity-75 [&_div]:-translate-y-px"
                                    :text="'&times;'"
                                    :aria-label="__('Remove condition')"
                                    @click="remove(index)"
                                />
                            </div>
                        </div>
                    </template>

                    <ol v-if="hasConditions && !isCustom">
                        <Condition
                            v-model:condition="conditions[index]"
                            :suggestable-fields="suggestableFields"
                            :exclude-handle="config?.handle"
                            :size
                        >
                            <template v-if="$slots['field-option']" #field-option="slotProps">
                                <slot name="field-option" v-bind="slotProps" />
                            </template>
                            <template v-if="$slots['field-selected']" #field-selected="slotProps">
                                <slot name="field-selected" v-bind="slotProps" />
                            </template>
                        </Condition>
                    </ol>
                </li>
            </ol>

            <Button
                v-if="hasConditions && !isCustom"
                :size
                variant="subtle"
                class="ms-4 bg-transparent!"
                :text="__('+ Add Condition')"
                @click="add"
            />
        </div>

        <div v-if="isCustom && hasConditions" class="mt-4 ms-4">
            <Input
                v-model="customMethod"
                :size
                :placeholder="__('Custom method name')"
            />
        </div>

        <div v-if="showAlwaysSave" data-always-save-decoration class="mt-8 mb-6 pt-4 flex flex-col gap-4 @md:flex-row @md:gap-6 border-t border-dashed border-gray-300 dark:border-gray-700">
            <Field
                class="@md:flex-1"
                :label="__('Always Save')"
                :instructions="__('messages.field_conditions_always_save_instructions')"
            >
                <Switch v-model="alwaysSave" />
            </Field>

            <Field
                class="@md:flex-1"
                :label="__('Reserve Space When Hidden')"
                :instructions="__('messages.field_conditions_reserve_space_when_hidden_instructions')"
            >
                <Switch v-model="reserveSpaceWhenHidden" />
            </Field>
        </div>
    </div>
</template>

<style>
[data-ui-card] [data-always-save-decoration] {
    margin-top: 1.5rem;
    margin-bottom: 0;
    padding-top: 0;
    border-top-width: 0;
}
</style>
