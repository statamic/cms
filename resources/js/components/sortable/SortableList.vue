<script>
import { Sortable, Plugins } from '@shopify/draggable'

function move(items, oldIndex, newIndex) {
    const itemRemovedArray = [
        ...items.slice(0, oldIndex),
        ...items.slice(oldIndex + 1, items.length)
    ]

    return [
        ...itemRemovedArray.slice(0, newIndex),
        items[oldIndex],
        ...itemRemovedArray.slice(newIndex, itemRemovedArray.length)
    ]
}

export default {

    props: {
        value: {
            required: true,
        },
        itemClass: {
            default: 'sortable-item',
        },
        handleClass: {
            default: 'sortable-handle',
        },
        appendTo: {
            default: null,
        },
        options: {
            default: () => {}
        },
        vertical: {
            type: Boolean
        },
        constrainDimensions: {
            type: Boolean
        },
        disabled: {
            type: Boolean,
            default: false
        },
    },

    computed: {

        computedOptions() {
            let options = Object.assign({}, {
                draggable: `.${CSS.escape(this.itemClass)}`,
                handle: `.${CSS.escape(this.handleClass)}`,
                delay: 200,
                swapAnimation: { vertical: this.vertical, horizontal: !this.vertical },
                plugins: [Plugins.SwapAnimation],
                mirror: {
                    constrainDimensions: this.constrainDimensions
                },
            }, this.options);

            if (this.vertical) {
                options.mirror.xAxis = false;
            }

            if (this.appendTo) {
                options.mirror.appendTo = this.appendTo
            }

            return options;
        }

    },

    provide() {
        return {
            itemClass: this.itemClass,
            handleClass: this.handleClass,
        }
    },

    render() {
        return this.$scopedSlots.default({
            items: this.value,
        })
    },

    mounted() {
        if (this.disabled) {
            return;
        }

        const sortable = new Sortable(this.$el, this.computedOptions);

        sortable.on('drag:start', () => this.$emit('dragstart'));
        sortable.on('drag:stop', () => this.$emit('dragend'));

        sortable.on('sortable:stop', ({ oldIndex, newIndex }) => {
            this.$emit('input', move(this.value, oldIndex, newIndex))
        })

        this.$on('hook:destroyed', () => {
            sortable.destroy()
        })
    }

}
</script>
