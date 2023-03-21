<template>
    <div :class="{'popover-open': isOpen}" v-on-clickaway="clickawayClose" @mouseleave="leave">
        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen" v-if="$scopedSlots.default">
            <slot name="trigger"></slot>
        </div>

        <portal
            v-if="isOpen"
            :to="portalTargetName"
            :target-class="`popover-container ${targetClass || ''}`"
        >
            <div :class="`${isOpen ? 'popover-open' : ''}`">
                <div ref="popover" class="popover" v-if="!disabled">
                    <div class="popover-content bg-white shadow-popover rounded-md">
                        <slot :close="close" :after-closed="afterClosed" />
                    </div>
                </div>
            </div>
        </portal>

    </div>
</template>

<script>
import { mixin as clickaway } from 'vue-clickaway';
import { computePosition, flip, shift, offset, autoUpdate } from '@floating-ui/dom';

export default {

    mixins: [ clickaway ],

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
        fixed: {
            type: Boolean
        },
    },

    data() {
        return {
            isOpen: false,
            escBinding: null,
            closedCallbacks: [],
            cleanupAutoUpdater: null,
            portalTarget: null,
        }
    },

    computed: {
        portalTargetName() {
            return this.portalTarget ? this.portalTarget.name : null;
        },
        targetClass() {
            return this.$vnode.data.staticClass;
        }
    },

    created() {
        this.createPortalTarget();
    },

    beforeDestroy() {
        this.destroyPortalTarget();
    },

    methods: {
        computePosition() {
            computePosition(this.$refs.trigger, this.$refs.popover, {
                placement: this.placement,
                strategy: this.fixed ? 'fixed' : 'absolute',
                middleware: [
                    offset({ mainAxis: this.offset[0], crossAxis: this.offset[1] }),
                    flip(), // If you place it on the right, and there's not enough room, it'll flip to the left, etc.
                    shift({ padding: 5 }), // If it'll end up positioned offscreen, it'll shift it enough to display it fully.
                ],
            }).then(({ x, y, strategy }) => {
                Object.assign(this.$refs.popover.style, {
                    position: strategy,
                    transform: `translate(${Math.round(x)}px, ${Math.round(y)}px)`, // Round to avoid blurry text
                });
            });
        },
        toggle() {
            this.isOpen ? this.close() : this.open();
        },
        open() {
            this.isOpen = true;
            this.escBinding = this.$keys.bind('esc', e => this.close());
            this.$nextTick(() => {
                this.cleanupAutoUpdater = autoUpdate(this.$refs.trigger, this.$refs.popover, this.computePosition);
            });
        },
        clickawayClose() {
            if (this.clickaway) {
                this.close();
            }
        },
        close() {
            if (!this.isOpen) return;

            this.isOpen = false;
            if (this.escBinding) {
                this.escBinding.destroy();
            }
            this.$emit('closed');
            this.cleanupAutoUpdater();
        },
        leave() {
            if (this.autoclose) {
                this.close();
            }
        },
        destroyPopper() {
            // run any after-closed callbacks
            this.closedCallbacks.forEach(callback => callback());
        },
        afterClosed(callback) {
            this.closedCallbacks.push(callback);
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
        }
    }
}
</script>
