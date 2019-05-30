<template>
    <button @click="selectAndClose">
        <template v-if="text">{{ text }}</template>
        <slot v-else></slot>
    </button>
</template>

<script>
export default {

    props: ['text', 'redirect'],

    methods: {
        selectAndClose() {
            if (this.redirect) {
                location.href = this.redirect;
                return;
            }

            this.$emit('click');

            this.$parent.close();
        }
    }

}
</script>
