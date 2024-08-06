<template>
    <div class="toggle-fieldtype-wrapper">
        <toggle-input
            :model-value="modelValue"
            @update:model-value="update"
            :read-only="isReadOnly"
            :id="fieldId"
        />
        <label v-if="inlineLabel" class="inline-label" v-html="$filters.markdown(__(inlineLabel))"></label>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

    computed: {
        inlineLabel() {
            return this.modelValue ? (this.config.inline_label_when_true || this.config.inline_label) : this.config.inline_label;
        },
        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return (this.modelValue ? '✓' : '✗') + ' ' + __(this.config.display);
        }
    }
};
</script>
