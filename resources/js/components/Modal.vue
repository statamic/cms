<template>

    <portal :to="portal">
        <vue-modal v-bind="modalProps" @closed="modalClosed">
            <slot :close="close" />
        </vue-modal>
    </portal>

</template>

<script>
export default {

    props: {
        name: { type: String, required: true },
        adaptive: { type: Boolean, default: true },
        draggable: { type: Boolean, default: false },
        pivotY: { type: Number, default: 0.1 },
        height: { default: 'auto' },
        width: {}
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
                adaptive: this.adaptive,
                clickToClose: false,
                draggable: this.draggable,
                height: this.height,
                pivotY: this.pivotY,
                width: this.width
            }
        }

    },

    mounted() {
        this.portal = `modal-${this.$modals.count()}`;
        this.$modals.open(this.name);
    },

    destroyed() {
        this.close();
    },

    methods: {

        modalClosed(event) {
            this.close();
        },

        close() {
            this.$modals.remove(this.name);
            this.$emit('closed');
        }

    }

}
</script>
