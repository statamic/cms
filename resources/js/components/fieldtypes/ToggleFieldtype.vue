<template>
    <div class="toggle-fieldtype-wrapper">
        <toggle-input :value="value" @input="update" :read-only="isReadOnly" :id="fieldId" />
        <label v-if="config.inline_label" class="inline-label" v-html="$options.filters.markdown(config.inline_label)"></label>
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    computed: {

        replicatorPreview() {
            return (this.value ? '✓' : '✗') + ' ' + this.config.display;
        }

    }
};
</script>
