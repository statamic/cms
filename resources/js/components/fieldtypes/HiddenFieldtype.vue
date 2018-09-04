<template>
    <input type="hidden" :name="name" :value="value" />
</template>

<script>
export default {
    mixins: [Fieldtype]
};
</script>
