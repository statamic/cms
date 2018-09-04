<template>
    <input type="hidden" :name="config.handle" :value="value" />
</template>

<script>
export default {
    mixins: [Fieldtype]
};
</script>
