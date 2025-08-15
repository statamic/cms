<template>
    <Input
        ref="input"
        :model-value="value"
        :classes="config.classes"
        :focus="shouldFocus"
        :autocomplete="config.autocomplete"
        :autoselect="config.autoselect"
        :type="config.input_type"
        :read-only="isReadOnly"
        :prepend="__(config.prepend)"
        :append="__(config.append)"
        :limit="config.character_limit"
        :placeholder="__(config.placeholder)"
        :name="name"
        :id="id"
        :direction="config.direction"
        @update:model-value="inputUpdated"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script setup>
import { Fieldtype } from '@statamic/cms';
import { Input } from '@/components/ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const {
    name,
    isReadOnly,
    update,
    updateDebounced,
    expose
} = Fieldtype.use(emit, props);

const shouldFocus = computed(() => {
    if (props.config.focus === false) {
        return false;
    }

    return props.config.focus || name.value === 'title' || name.value === 'alt';
});

function inputUpdated(value) {
    return !props.config.debounce ? update(value) : updateDebounced(value);
}

defineExpose(expose);
</script>
