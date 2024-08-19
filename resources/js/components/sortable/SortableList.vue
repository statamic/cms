<script>
import { Sortable, Plugins } from '@shopify/draggable'
import { closestVm } from '../../bootstrap/globals';

const instances = {};

export default {

    props: {
        value: {
            required: true,
        },
        group: {
            default: null,
        },
        groupValidator: {
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
                if (!this.group) {
                    this.$emit('input', arrayMove(this.value, oldIndex, newIndex));
                    return;           
                }
                const payload = { oldIndex, newIndex };
                if (newContainer === this.$el && oldContainer === this.$el) {
                    this.$emit('input', { operation: 'move', oldList: this, newList: this, ...payload });
                } else if (newContainer === this.$el) {
                    this.$emit('input', { operation: 'add', oldList: closestVm(oldContainer, 'sortable-list'), newList: this, ...payload });
                } else if (oldContainer === this.$el) {
                    this.$emit('input', { operation: 'remove', oldList: this, newList: closestVm(newContainer, 'sortable-list'), ...payload });
                }
            });

            if (this.group && this.groupValidator) {
                this.sortable.on('sortable:sort', (event) => {
                    const { dragEvent } = event;
                    const { sourceContainer, overContainer, source } = dragEvent;
                    if (overContainer !== this.$el || sourceContainer === this.$el) {
                        return;
                    }
                    if (!this.groupValidator({ source })) {
                        event.cancel();
                    }
                });
                this.sortable.on('sortable:start', (event) => {
                    const { dragEvent } = event;
                    const { source } = dragEvent;
                    const valid = this.groupValidator({ source });
                    this.$emit('groupstart', { valid });
                });
                this.sortable.on('sortable:stop', () => {
                    this.$emit('groupend');
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
