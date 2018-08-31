<template>
    <input
        class="form-control"
        :name="name"
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
        name: {},
        disabled: { default: false },
        placeholder: { required: false },
        type: { default: "text" },
        value: { required: true },
    }
}
</script>
