<template>
    <button @click="selectAndClose">
        <!-- Pass prop text OR child component into slot -->
        <template v-if="text">{{ text }}</template>
        <slot></slot>
    </button>
</template>

<script>
export default {

    props: ['text', 'redirect', 'externalLink'],

    methods: {
        selectAndClose($event) {
            if (this.redirect) {
                location.href = this.redirect;
                return;
            }

            if (this.externalLink) {
                window.open(this.externalLink, '_blank');
                return;
            }

            this.$emit('click', $event);

            this.$parent.close();
        }
    }

}
</script>
