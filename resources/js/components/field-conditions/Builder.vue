<script setup>
import { Button, Combobox, Field, Icon, Input, Switch, Text } from '@ui';
import { computed, onMounted, ref, watch } from 'vue';
import { nanoid as uniqid } from 'nanoid';
import Condition from './Condition.vue';
import Converter from './Converter.js';
import { KEYS } from './Constants.js';

const emit = defineEmits(['updated', 'updated-always-save']);

const props = defineProps({
    config: { type: Object, required: true },
    suggestableFields: { type: Array, required: true },
    allowCustomConditions: { type: Boolean, default: true },
    size: { type: String, default: 'base' },
});

const when = ref('always');
const type = ref('all');
const customMethod = ref(null);
const conditions = ref([]);
const alwaysSave = ref(false);

const whenOptions = [
    { label: __('Always show'), value: 'always', icon: 'eye' },
    { label: __('Show when'), value: 'if', icon: 'eye' },
    { label: __('Hide when'), value: 'unless', icon: 'eye-closed' },
];

const joinOptions = [
    { label: __('All of the conditions pass'), short_label: __('And'), value: 'all' },
    { label: __('Any of the conditions pass'), short_label: __('Or'), value: 'any' },
    // { label: __('Custom method'), short_label: __('Custom'), value: 'custom' },
];

const isCustom = computed(() => type.value === 'custom');
const hasConditions = computed(() => when.value !== 'always');

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
    const result = {};
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

watch(saveableConditions, (conditions) => emit('updated', conditions), { deep: true });
watch(alwaysSave, (value) => emit('updated-always-save', value));

onMounted(() => {
    getInitialConditions();
    getInitialAlwaysSaveState();
    if (conditions.value.length === 0) add();
});
</script>

<template>
    <div class="w-full">
        <div data-logic-text class="logic-text">
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
                            <Button
                                :size
                                inset
                                variant="subtle"
                                class="mb-2.5 mt-[0.5px] p-2.5 size-6 ms-0.25 rounded-full [&_div]:-translate-y-[1px] [&_svg]:opacity-80"
                                icon="indent-right"
                                :aria-label="__('Indent right')"
                                :title="__('Indent right')"
                            />
                            <Button
                                :size
                                inset
                                variant="subtle"
                                class="mb-2.5 mt-[0.5px] p-2.5 size-6 ms-0.25 rounded-full [&_div]:-translate-y-[1px]"
                                :text="'×'"
                                :aria-label="__('Remove condition')"
                                @click="remove(index)"
                            />
                        </div>
                    </template>

                    <ol v-if="hasConditions && !isCustom">
                        <Condition
                            v-model:condition="conditions[index]"
                            :conditions="conditions"
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

        <Field
            class="mt-6"
            :label="__('Always Save')"
            :instructions="__('messages.field_conditions_always_save_instructions')"
        >
            <Switch v-model="alwaysSave" />
        </Field>
    </div>
</template>
