<template>
    <Input
        ref="input"
        :model-value="value"
        :classes="config.classes"
        :focus="config.focus || name === 'title' || name === 'alt'"
        :autocomplete="config.autocomplete"
        :autoselect="config.autoselect"
        :type="config.input_type"
        :disabled="isReadOnly"
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
import { Fieldtype } from 'statamic';
import { Input } from '@statamic/ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const {
    name,
    isReadOnly,
    update,
    updateDebounced,
    expose
} = Fieldtype.use(emit, props);

function inputUpdated(value) {
    return !props.config.debounce ? update(value) : updateDebounced(value);
}

defineExpose(expose);
</script>
