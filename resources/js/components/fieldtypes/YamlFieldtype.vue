<template>
    <CodeEditor
        ref="codeEditor"
        :allow-mode-selection="false"
        :disabled="config.disabled"
        :indent-type="'spaces'"
        :mode="'yaml'"
        :model-value="value"
        :read-only="isReadOnly"
        :show-mode-label="false"
        :tab-size="2"
        :theme="config.theme"
        @update:model-value="update"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { CodeEditor } from '@statamic/cms/ui';

export default {
    mixins: [Fieldtype],

    components: { CodeEditor },
};
</script>
