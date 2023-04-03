<template>
    <div :class="{'popover-open': isOpen}" @mouseleave="leave">
        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen" v-if="$scopedSlots.default">
            <slot name="trigger"></slot>
        </div>

        <portal
            :to="portalTargetName"
            :target-class="`popover-container ${targetClass || ''}`"
        >
        <provider :variables="provide">
            <div :class="`${isOpen ? 'popover-open' : ''}`">
                <div ref="popover" class="popover" v-if="!disabled" v-on-clickaway="clickawayClose">
                    <div class="popover-content bg-white shadow-popover rounded-md">
                        <slot :close="close" />
                    </div>
                </div>
            </div>
        </provider>
        </portal>

    </div>
</template>

<script>
import { mixin as clickaway } from 'vue-clickaway';
import { computePosition, flip, shift, offset, autoUpdate } from '@floating-ui/dom';
import Provider from './live-preview/Provider.vue';

export default {

    mixins: [ clickaway ],

    components: {
        Provider,
    },

    props: {
        autoclose: {
            type: Boolean,
            default: false
        },
        clickaway: {
            type: Boolean,
            default: true
        },
        disabled: {
            type: Boolean,
            default: false
        },
        offset: {
            type: Array,
            default: () => [10, 0]
        },
        placement: {
            type: String,
            default: 'bottom-end',
        },
    },

    data() {
        return {
            isOpen: false,
            escBinding: null,
            cleanupAutoUpdater: null,
            portalTarget: null,
            provide: {
                popover: this.makeProvide(),
            },
        }
    },

    computed: {

        portalTargetName() {
            return this.portalTarget ? this.portalTarget.id : null;
        },

        targetClass() {
            return this.$vnode.data.staticClass;
        }

    },

    created() {
        this.portalTarget = this.$portals.create('popover');
    },

    beforeDestroy() {
        this.portalTarget.destroy();
    },

    methods: {

        computePosition() {
            if (! this.$refs.trigger) return;

            computePosition(this.$refs.trigger.firstChild, this.$refs.popover, {
                placement: this.placement,
                middleware: [
                    offset({ mainAxis: this.offset[0], crossAxis: this.offset[1] }),
                    flip(), // If you place it on the right, and there's not enough room, it'll flip to the left, etc.
                    shift({ padding: 5 }), // If it'll end up positioned offscreen, it'll shift it enough to display it fully.
                ],
            }).then(({ x, y }) => {
                Object.assign(this.$refs.popover.style, {
                    transform: `translate(${Math.round(x)}px, ${Math.round(y)}px)`, // Round to avoid blurry text
                });
            });
        },

        toggle() {
            this.isOpen ? this.close() : this.open();
        },

        open() {
            if (this.disabled) return;

            this.isOpen = true;
            this.escBinding = this.$keys.bind('esc', e => this.close());
            this.$nextTick(() => {
                this.cleanupAutoUpdater = autoUpdate(this.$refs.trigger.firstChild, this.$refs.popover, this.computePosition);
                this.$emit('opened');

                this.$refs.popover.addEventListener('transitionend', () => {
                    this.$emit('opened');
                }, { once: true });
            });
        },

        clickawayClose(e) {
            // If disabled or closed, do nothing.
            if (! this.clickaway || ! this.isOpen) return;

            // If clicking within the popover, or inside the trigger, do nothing.
            // These need to be checked separately, because the popover contents away.
            if (this.$refs.popover.contains(e.target) || this.$el.contains(e.target)) return;

            this.close();
            this.$emit('clicked-away', e);
        },

        close() {
            if (! this.isOpen) return;

            this.isOpen = false;
            this.$emit('closed');
            this.cleanupAutoUpdater();

            if (this.escBinding) this.escBinding.destroy();
        },

        leave() {
            if (this.autoclose) this.close();
        },

        createPortalTarget() {
            let key = `popover-${this._uid}`;
            let portalTarget = { key, name: key };
            this.$root.portals.push(portalTarget);
            this.portalTarget = portalTarget;
        },

        destroyPortalTarget() {
            const i = _.findIndex(this.$root.portals, (portal) => portal.key === this.portalTarget.key);
            this.$root.portals.splice(i, 1);
        },

        makeProvide() {
            const provide = {};
            Object.defineProperties(provide, {
                vm: { get: () => this },
            });
            return provide;
        }
    }
}
</script>
