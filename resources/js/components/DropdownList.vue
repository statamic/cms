<template>
    <popover
        class="dropdown-list"
        :trigger-class="className"
        :disabled="disabled"
        :placement="placement"
        :autoclose="autoclose"
        :offset="offset"
        @opened="$emit('opened')"
        @closed="$emit('closed')"
    >
        <template #trigger>
            <slot name="trigger">
                <button class="rotating-dots-button" :aria-label="__('Open Dropdown')" type="button">
                    <svg class="rotating-dots fill-current" width="12" viewBox="0 0 24 24">
                        <circle cx="3" cy="12" r="3" />
                        <circle cx="12" cy="12" r="3" />
                        <circle cx="21" cy="12" r="3" />
                    </svg>
                </button>
            </slot>
        </template>
        <template #default><slot /></template>
    </popover>
</template>

<script>
export default {
    props: {
        disabled: {
            type: Boolean,
            default: false,
        },
        placement: {
            type: String,
            default: 'bottom-end',
        },
        autoclose: {
            type: Boolean,
            default: false,
        },
        offset: {
            type: Array,
            default: () => [10, 0],
        },
        class: {
            type: String,
        },
    },
    computed: {
        strategy() {
            return this.scroll ? 'fixed' : 'absolute';
        },
        className() {
            return this.class;
        },
    },
};
</script>
