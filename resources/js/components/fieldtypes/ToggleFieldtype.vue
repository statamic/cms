<template>
    <div class="toggle-fieldtype-wrapper">
        <toggle-input :value="value" @input="update" />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],
};
</script>
