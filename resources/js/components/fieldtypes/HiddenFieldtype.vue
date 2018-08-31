<template>
    <input type="hidden" :name="config.handle" :value="initialValue" />
</template>

<script>
export default {
    mixins: [Fieldtype]
};
</script>
