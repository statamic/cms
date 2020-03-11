<template>
    <input
        type="text"
        ref="input"
        placeholder="Search..."
        v-model="searchQuery"
        @input="emitEvent"
        @keyup.esc="reset"
        class="input-text flex-1 bg-white text-sm">
</template>

<script>
export default {

    props: ['value'],

    data() {
        return {
            searchQuery: this.value,
        }
    },

    watch: {
        value(value) {
            this.searchQuery = value;
        }
    },

    methods: {
        emitEvent: _.debounce(function (event) {
            this.$emit('input', event.target.value);
            this.$events.$emit('search-query-changed', event.target.value);
        }, 300),

        reset() {
            this.searchQuery = '';

            this.$emit('input', this.searchQuery);
            this.$events.$emit('search-query-changed', this.searchQuery);
        },

        focus() {
            this.$refs.input.focus();
        }
    },
}
</script>
