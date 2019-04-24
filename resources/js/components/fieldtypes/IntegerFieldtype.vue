<template>
    <text-input
        type="number"
        tabindex="0"
        :name="name"
        :autofocus="autofocus"
        :value="value"
        :is-read-only="isReadOnly"
        @input="update"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>

export default {

    mixins: [Fieldtype],

    props: ['autofocus']

};
</script>
