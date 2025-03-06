<template>
    <div class="toggle-fieldtype-wrapper">
        <toggle-input :value="value" @input="update" :read-only="isReadOnly" :id="fieldId" />
        <label v-if="inlineLabel" class="inline-label" v-html="$markdown(__(inlineLabel))"></label>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

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
