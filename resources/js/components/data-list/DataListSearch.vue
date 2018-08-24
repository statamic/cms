<template>
    <input
        type="text"
        class="data-list-search-input"
        placeholder="Search..."
        :value="value"
        @input="$emit('input', $event.target.value)"
    >
</template>

<script>
export default {
    props: ['value']
    //TODO: Debouce the input
}
</script>
