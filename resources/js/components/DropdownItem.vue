<template>
    <a :href="href" :target="target" @click="selectAndClose">
        <!-- Pass prop text OR child component into slot -->
        <template v-if="text">{{ text }}</template>
        <slot></slot>
    </a>
</template>

<script>
export default {
    emits: ['click'],

    props: ['text', 'redirect', 'externalLink'],

    inject: ['popover'],

    computed: {
        href() {
            return this.redirect || this.externalLink;
        },

        target() {
            return this.externalLink ? '_blank' : null;
        },
    },

    methods: {
        selectAndClose($event) {
            if (this.href) {
                return;
            }

            this.$emit('click', $event);

            this.popover.vm.close();
        },
    },
};
</script>
