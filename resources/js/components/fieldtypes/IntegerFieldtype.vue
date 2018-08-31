<template>
    <text-input
        type="number"
        tabindex="0"
        :autofocus="autofocus"
        :value="initialValue"
        @input="update"
    />
</template>

<script>

export default {

    mixins: [Fieldtype],

    props: ['autofocus']

};
</script>
