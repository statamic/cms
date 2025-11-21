<script>
import { Sortable, Plugins, Draggable } from '@shopify/draggable';
import uniqid from 'uniqid';

const groups = {};
const lists = {};

export default {
    emits: ['dragstart', 'dragend', 'update:model-value'],

    props: {
        modelValue: {
            required: true,
        },
        owner: {
            default: null,
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
            default: true,
        },
        appendTo: {
            default: null,
        },
        options: {
            default: () => {},
        },
        vertical: {
            type: Boolean,
        },
        constrainDimensions: {
            type: Boolean,
        },
        delay: {
            type: Number,
            default: 0,
        },
        distance: {
            type: Number,
            default: 0,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        animate: {
            type: Boolean,
            default: true,
        },
    },

    data() {
        return {
            sortable: null,
            groupId: this.group || uniqid(),
            listId: uniqid(),
        };
    },

    computed: {
        computedOptions() {
            let plugins = [];
            if (this.animate) plugins.push(Plugins.SwapAnimation);

            let options = Object.assign(
                {},
                {
                    draggable: `.${CSS.escape(this.itemClass)}`,
                    handle: `.${CSS.escape(this.handleClass)}`,
                    delay: this.delay,
                    distance: this.distance,
                    swapAnimation: { vertical: this.vertical, horizontal: !this.vertical },
                    plugins,
                    mirror: {
                        constrainDimensions: this.constrainDimensions,
                    },
                    exclude: {
                        plugins: [Draggable.Plugins.Focusable]
                    }
                },
                this.options,
            );

            if (this.vertical) {
                options.mirror.xAxis = false;
            }

            if (this.appendTo) {
                options.mirror.appendTo = this.appendTo;
            }

            return options;
        },
    },

    provide() {
        return {
            itemClass: this.itemClass,
            handleClass: this.handleClass,
        };
    },

    render() {
        return this.$slots.default({
            items: this.modelValue,
        })[0];
    },

    mounted() {
        if (this.disabled) {
            return;
        }

        this.setupSortableList();
    },

    unmounted() {
        this.destroySortableList();
    },

    methods: {
        setupSortableList() {
            this.sortable = this.connectSortable();
            this.sortable.on('drag:start', () => this.$emit('dragstart'));
            this.sortable.on('drag:stop', () => this.$emit('dragend'));
            this.sortable.on('sortable:stop', (event) => {
                const { oldIndex, newIndex, oldContainer, newContainer } = event;
                if (!this.group) {
                    this.$emit('update:model-value', arrayMove(this.value, oldIndex, newIndex));
                    return;           
                }
                const payload = { oldIndex, newIndex };
                if (newContainer === this.$el && oldContainer === this.$el) {
                    this.$emit('update:model-value', { operation: 'move', oldList: this, newList: this, ...payload });
                } else if (newContainer === this.$el) {
                    this.$emit('update:model-value', { operation: 'add', oldList: lists[oldContainer.dataset.listId], newList: this, ...payload });
                } else if (oldContainer === this.$el) {
                    this.$emit('update:model-value', { operation: 'remove', oldList: this, newList: lists[newContainer.dataset.listId], ...payload });
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
            if (this.mirror === false) {
                this.sortable.on('mirror:create', (e) => e.cancel());
            }
        },

        destroySortableList() {
            this.disconnectSortable();
        },

        connectSortable() {
            this.$el.dataset.listId = this.listId;
            lists[this.listId] = this;
            if (!groups[this.groupId]) {
                groups[this.groupId] = new Sortable(this.$el, this.computedOptions);
            } else {
                groups[this.groupId].addContainer(this.$el);
            }
            return groups[this.groupId];
        },

        disconnectSortable() {
            delete lists[this.listId];
            if (groups[this.groupId]) {
                groups[this.groupId].removeContainer(this.$el);
                if (groups[this.groupId].containers.length === 0) {
                    groups[this.groupId].destroy();
                    delete groups[this.groupId];
                }
            }
        },
    },

    watch: {
        disabled(disabled) {
            disabled ? this.destroySortableList() : this.setupSortableList();
        },
    },
};
</script>
