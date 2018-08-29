<template>
    <input
        class="form-control"
        :value="value"
        :type="type"
        :disabled="disabled"
        :placeholder="placeholder"
        @input="$emit('input', $event.target.value)"
    >
</template>

<script>
export default {
    props: {
        disabled: { default: false },
        placeholder: { required: false },
        type: { default: "text" },
        value: { required: true },
    }
}
</script>
