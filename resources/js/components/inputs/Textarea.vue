<template>

    <textarea
        class="form-control"
        :value="value"
        :disabled="disabled"
        :placeholder="placeholder"
        @input="$emit('input', $event.target.value)"
        v-elastic
    />

</template>

<script>
export default {
    props: {
        disabled: { default: false },
        placeholder: { required: false },
        value: { required: true },
    }
}
</script>
