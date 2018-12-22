<template>

    <portal :to="portal">
        <vue-modal v-bind="modalProps" @closed="modalClosed">
            <slot />
        </vue-modal>
    </portal>

</template>

<script>
export default {

    props: {
        name: { type: String, required: true },
        width: {},
        height: {},
    },

    data() {
        return {
            portal: null,
        }
    },

    computed: {

        modalProps() {
            return {
                name: this.name,
                width: this.width,
                height: this.height,
                clickToClose: false,
            }
        }

    },

    mounted() {
        this.portal = `modal-${this.$modals.count()}`;
        this.$modals.open(this.name);
    },

    destroyed() {
        this.destroy();
    },

    methods: {

        modalClosed(event) {
            this.destroy();
        },

        destroy() {
            this.$modals.remove(this.name);
            this.$emit('closed');
        }

    }

}
</script>
