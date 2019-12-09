<template>
    <v-select
        ref="input"
        label="title"
        :close-on-select="false"
        :disabled="readOnly"
        :multiple="true"
        :taggable="true"
        :value="value"
        @input="update($event)"
    />
</template>

<script>
export default {

    mixins: [Fieldtype],

    methods: {

        focus() {
            this.$refs.input.searchEl.focus();
        }

    }
};
</script>
