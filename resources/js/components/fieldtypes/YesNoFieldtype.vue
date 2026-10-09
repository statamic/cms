<script setup>
import { computed, useTemplateRef } from 'vue';
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { RadioGroup, Radio, Icon } from '@/components/ui';
import HasInputOptions from '@/components/fieldtypes/HasInputOptions.js';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, isReadOnly, update } = Fieldtype.use(emit, props);

const options = computed(() => HasInputOptions.methods.normalizeInputOptions(props.meta.options || props.config.options));

const iconName = (optionValue, selected) => {
    if (! selected) {
        return 'circle';
    }

    return optionValue === 'yes' ? 'checkmark-circle-filled' : 'delete-circle-filled';
};

const radio = useTemplateRef('radio');
const focus = () => radio.value.focus();

defineExpose({ ...expose, focus });
</script>

<template>
    <RadioGroup appearance="chips" :model-value="value" @update:model-value="update" ref="radio">
        <Radio
            v-for="(option, index) in options"
            :disabled="config.disabled"
            :key="index"
            :label="__(option.label) || option.value"
            :read-only="isReadOnly"
            :value="option.value"
            item-class="relative top-0.5"
        >
            <template #item>
                <Icon
                    :name="iconName(option.value, value === option.value)"
                    class="size-4 cursor-default"
                    :class="value === option.value ? 'text-ui-accent-bg' : 'text-gray-400 dark:text-gray-500'"
                />
            </template>
        </Radio>
    </RadioGroup>
</template>
