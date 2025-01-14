<template>

    <portal name="modal">
        <VueFinalModal
            v-model="open"
            v-bind="modalProps"
            @opened="modalOpened"
            @closed="modalClosed"
        >
            <div :style="styling" class="max-h-[90vh]">
                <slot :close="close" />
            </div>
        </VueFinalModal>
    </portal>

</template>

<script>
import uniqid from 'uniqid';
import { VueFinalModal } from 'vue-final-modal';

export default {

    components: {
        VueFinalModal,
    },

    props: {
        adaptive: { type: Boolean, default: true },
        draggable: { default: false },
        clickToClose: { type: Boolean, default: false },
        focusTrap: {type: Boolean, default: true},
        height: { default: 'auto' },
        width: { default: 600 },
    },

    data() {
        return {
            modal: null,
            name: uniqid(),
            open: true,
        }
    },

    computed: {

        modalProps() {
            return {
                modalId: this.name,
                clickToClose: this.clickToClose,
                focusTrap: this.focusTrap,
                teleportTo: false,
                class: 'flex items-start justify-center pt-[5%]',
                overlayTransition: 'vfm-fade',
                contentTransition: 'vfm-slide-up'
            }
        },

        styling() {
            return {
                width: typeof(this.width) === 'number' ? `${this.width}px` : this.width,
                height: typeof(this.height) === 'number' ? `${this.height}px` : this.height,
            }
        }

    },

    beforeUnmount() {
        this.close();
    },

    methods: {

        modalOpened(event) {
            this.$emit('opened');
        },

        modalClosed(event) {
            this.close();
        },

        close() {
            this.open = false;
            this.$wait(300).then(() => this.$emit('closed'));
        }

    }

}
</script>
