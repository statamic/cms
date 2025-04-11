<template>
    <div class="flex items-center gap-2">
        <Switch :model-value="value" @update:model-value="update" :id="fieldId" :disabled="isReadOnly" />
        <ui-heading v-if="inlineLabel" v-html="$markdown(__(inlineLabel))" />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import Switch from '@statamic/components/ui/Switch.vue';

export default {
    mixins: [Fieldtype],

    components: {
        Switch,
    },

    computed: {
        inlineLabel() {
            return this.value
                ? this.config.inline_label_when_true || this.config.inline_label
                : this.config.inline_label;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return (this.value ? '✓' : '✗') + ' ' + __(this.config.display);
        },
    },
};
</script>
