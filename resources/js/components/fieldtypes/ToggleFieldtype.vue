<template>
    <div class="toggle-fieldtype-wrapper">
        <toggle-input :value="value" @input="update" :read-only="isReadOnly" :id="fieldId" />
        <label v-if="config.inline_label" class="inline-label" v-html="$options.filters.markdown(__(config.inline_label))"></label>
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    computed: {

        replicatorPreview() {
            return (this.value ? '✓' : '✗') + ' ' + __(this.config.display);
        }

    }
};
</script>
