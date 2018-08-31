<template>
    <select class="select-input" @change="change" :value="value">
        <option v-text="placeholder" value="" v-if="placeholder" :selected="! value"></option>
        <option
            v-for="option in options"
            v-text="option.text"
            :value="option.value"
            :selected="option.value == value"
        ></option>
    </select>
</template>

<script>
export default {
    props: {
        disabled: { default: false },
        options: { default: []},
        placeholder: { required: false },
        value: { required: true },
    },
    methods: {
        change(event) {
            this.$emit('input', event.target.value)
        }
    }

}
</script>
