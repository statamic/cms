<template>
    <AutocompleteEditor
        :model-value="value"
        :options="meta.options"
        :inline="config.inline"
        :enable-line-breaks="config.enable_line_breaks"
        :buttons="config.buttons"
        :trigger="config.trigger"
        :placeholder="config.placeholder"
        :read-only="isReadOnly"
        @update:model-value="update"
    />
</template>

<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { AutocompleteEditor } from '@/components/ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update } = Fieldtype.use(emit, props);
</script>
