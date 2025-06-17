<template>
    <CodeEditor
        ref="codeEditor"
        :theme="config.theme"
        :disabled="isReadOnly"
        :tab-size="2"
        :indent-type="'spaces'"
        :allow-mode-selection="false"
        :show-mode-label="false"
        :mode="'yaml'"
        :model-value="value"
        @update:model-value="update"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { CodeEditor } from '@statamic/ui';

export default {
    mixins: [Fieldtype],

    components: { CodeEditor },
};
</script>
