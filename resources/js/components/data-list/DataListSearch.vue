<template>
    <input
        type="text"
        class="data-list-search-input"
        placeholder="Search..."
        @input="$emit('input', $event.target.value)"
    >
</template>

<script>
export default {
    //TODO: Debouce the input
}
</script>
