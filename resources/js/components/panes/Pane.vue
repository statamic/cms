<template>

    <portal to="pane">
        <transition name="pane-slide">
            <div class="w-96 bg-white shadow-lg fixed pin-t pin-r pin-b">
                <slot name="default" :close="close" />
            </div>
        </transition>
    </portal>

</template>

<script>
export default {

    data() {
        return {
            escBinding: null,
        }
    },

    created() {
        this.$panes.open(this);
        this.escBinding = this.$keys.bindGlobal('esc', this.close);
    },

    destroyed() {
        this.$panes.close(this);
        this.escBinding.destroy();
    },

    methods: {

        close() {
            this.$emit('closed');
        },

    }

}
</script>
