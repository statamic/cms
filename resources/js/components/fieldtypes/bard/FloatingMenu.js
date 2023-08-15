import { FloatingMenuPlugin } from './FloatingMenuPlugin'

export const FloatingMenu = {
    name: 'FloatingMenu',

    props: {
        editor: {
            type: Object,
            required: true,
        },
        shouldShow: {
            type: Function,
            default: null,
        },
        isShowing: {
            type: Boolean,
        }
    },

    data() {
        return {
            show: false,
            x: 0,
            y: 0
        }
    },

    watch: {
        editor: {
            immediate: true,
            handler(editor) {
                if (!editor) return;

                this.$nextTick(() => {
                    editor.registerPlugin(FloatingMenuPlugin({
                        pluginKey: 'floatingMenu',
                        editor,
                        vm: this,
                        element: this.$el,
                        shouldShow: this.shouldShow,
                    }))
                })
            },
        },

        isShowing(showing) {
            // Depending on the action, sometimes the menu visibility is modified from the
            // parent (e.g. on blur) and sometimes from the menu itself (e.g.) when moving the cursor.
            this.show = showing;
        },

        show(shown) {
            if (shown) {
                this.$emit('shown');
            } else {
                this.$emit('hidden');
            }
        }
    },

    render() {
        return this.$scopedSlots.default({
            x: this.x,
            y: this.y,
        });
    },

    beforeDestroy() {
        this.editor.unregisterPlugin('floatingMenu')
    },
}
