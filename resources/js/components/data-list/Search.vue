<template>
    <input
        type="text"
        class="data-list-search-input"
        placeholder="Search..."
        :value="value"
        @input="emitEvent"
    >
</template>

<script>
export default {
    props: ['value'],

    methods: {
        emitEvent: _.debounce(function (event) {
            this.$emit('input', event.target.value);
        }, 300)
    }
}
</script>
