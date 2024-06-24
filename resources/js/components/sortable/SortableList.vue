<script>
import { Plugins } from '@shopify/draggable'

function add(items, item, index) {
    return [
        ...items.slice(0, index),
        item,
        ...items.slice(index, items.length)
    ]
}

function remove(items, index) {
    return [
        ...items.slice(0, index),
        ...items.slice(index + 1, items.length)
    ]
}

function move(items, oldIndex, newIndex) {
    return add(remove(items, oldIndex), items[oldIndex], newIndex);
}

export default {

    props: {
        value: {
            required: true,
        },
        group: {
            default: null,
        },
        itemClass: {
            default: 'sortable-item',
        },
        handleClass: {
            default: 'sortable-handle',
        },
        mirror: {
            type: Boolean,
            default: true
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
        delay: {
            type: Number,
            default: 0
        },
        distance: {
            type: Number,
            default: 0
        },
        disabled: {
            type: Boolean,
            default: false
        },
        animate: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            sortable: null,
            sortableId: this.group || uniqid(),
        }
    },

    computed: {

        computedOptions() {
            let plugins = [];
            if (this.animate) plugins.push(Plugins.SwapAnimation);

            let options = Object.assign({}, {
                draggable: `.${CSS.escape(this.itemClass)}`,
                handle: `.${CSS.escape(this.handleClass)}`,
                delay: this.delay,
                distance: this.distance,
                swapAnimation: { vertical: this.vertical, horizontal: !this.vertical },
                plugins,
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

        this.setupSortableList();
    },

    methods: {
        setupSortableList() {
            this.sortable = this.$sortables.connect(this.sortableId, this.$el, this.computedOptions);

            this.sortable.on('drag:start', () => this.$emit('dragstart'));
            this.sortable.on('drag:stop', () => this.$emit('dragend'));

            this.sortable.on('sortable:stop', ({ newContainer, oldContainer, oldIndex, newIndex }) => {
                if (newContainer === this.$el && oldContainer === this.$el) {
                    this.$emit('input', move(this.value, oldIndex, newIndex));
                } else if (newContainer === this.$el) {
                    this.$emit('input', add(this.value, oldContainer.__vue__.value[oldIndex], newIndex));
                } else if (oldContainer === this.$el) {
                    this.$emit('input', remove(this.value, oldIndex));
                }
            })

            this.$on('hook:destroyed', () => {
                this.sortable.destroy()
            })

            if (this.mirror === false) {
                this.sortable.on('mirror:create', (e) => e.cancel());
            }
        },

        destroySortableList() {
            this.$sortables.disconnect(this.sortableId, this.$el, this.computedOptions);
        },
    },

    watch: {
        disabled(disabled) {
            disabled ? this.destroySortableList() : this.setupSortableList();
        },
    },

}
</script>
