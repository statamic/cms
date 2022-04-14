<template>
    <div class="toggle-fieldtype-wrapper">
        <toggle-input :value="value" @input="update" :read-only="isReadOnly" />
        <label v-if="config.inline_label" class="ml-1 font-normal">{{ config.inline_label }}</label>
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
