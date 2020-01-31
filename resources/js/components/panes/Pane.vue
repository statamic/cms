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

    created() {
        this.$panes.open(this);
    },

    destroyed() {
        this.$panes.close(this);
    },

    methods: {

        close() {
            this.$emit('closed');
        },

    },

    mounted() {
        this.$keys.bindGlobal(['esc'], e => {
            this.close();
        });
    }

}
</script>
