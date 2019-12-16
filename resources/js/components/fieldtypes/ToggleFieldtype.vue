<template>
    <div class="toggle-fieldtype-wrapper">
        <toggle-input :value="value" @input="update" :read-only="isReadOnly" />
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
