<script>
import { Sortable, Plugins } from '@shopify/draggable'

const instances = {};

export default {

    props: {
        value: {
            required: true,
        },
        group: {
            default: null,
        },
        groupDroppable: {
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
            instanceId: this.group || uniqid(),
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
            this.sortable = this.connectInstace(this.instanceId, this.$el, this.computedOptions);

            this.sortable.on('drag:start', () => this.$emit('dragstart'));
            this.sortable.on('drag:stop', () => this.$emit('dragend'));

            this.sortable.on('sortable:stop', (event) => {
                const { oldIndex, newIndex, oldContainer, newContainer } = event;
                if (oldContainer !== this.$el && newContainer !== this.$el) {
                    // Event doesn't concern this list
                    return;                    
                }
                this.$emit('sortablestop', event);
                this.$emit('input', arrayMove(this.value, oldIndex, newIndex));
            })

            if (this.group && this.groupDroppable) {
                this.sortable.on('sortable:sort', (event) => {
                    const { dragEvent } = event;
                    const { sourceContainer, overContainer, source } = dragEvent;
                    if (sourceContainer !== this.$el && overContainer !== this.$el) {
                        // Event doesn't concern this list
                        return;                    
                    }
                    if (!this.groupDroppable(event)) {
                        event.cancel();
                    }
                });
            }

            this.$on('hook:destroyed', () => {
                this.destroySortableList();
            })

            if (this.mirror === false) {
                this.sortable.on('mirror:create', (e) => e.cancel());
            }
        },

        destroySortableList() {
            this.disconnectInstace(this.instanceId, this.$el);
        },

        connectInstace(id, container, options) {
            if (!instances[id]) {
                instances[id] = new Sortable(container, options);
            } else {
                instances[id].addContainer(container);
            }
            return instances[id];
        },

        disconnectInstace(id, container) {
            if (instances[id]) {
                instances[id].removeContainer(container);
                if (instances[id].containers.length === 0) {
                    instances[id].destroy();
                    delete instances[id];
                }
            }
        },

    },

    watch: {
        disabled(disabled) {
            disabled ? this.destroySortableList() : this.setupSortableList();
        },
    },

}
</script>
