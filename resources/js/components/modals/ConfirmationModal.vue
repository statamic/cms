<template>
    <modal name="confirmation-modal" @opened="$emit('opened')">
        <form class="confirmation-modal flex h-full flex-col" @submit.prevent="submit">
            <header
                v-if="title"
                class="flex items-center justify-between rounded-t-lg border-b bg-gray-200 px-5 py-3 text-lg font-semibold dark:border-dark-900 dark:bg-dark-550"
            >
                {{ __(title) }}
            </header>
            <div class="relative flex-1 px-5 py-6 text-gray dark:text-dark-150">
                <slot name="body">
                    <p v-if="bodyText" v-text="bodyText" />
                    <slot v-else>
                        <p>{{ __('Are you sure?') }}</p>
                    </slot>
                </slot>
                <div
                    v-if="busy"
                    class="pointer-events-none absolute inset-0 flex select-none items-center justify-center bg-white bg-opacity-75 dark:bg-dark-700"
                >
                    <loading-graphic text="" />
                </div>
            </div>
            <div
                class="flex items-center justify-end rounded-b-lg border-t bg-gray-200 px-5 py-3 text-sm dark:border-dark-900 dark:bg-dark-550"
            >
                <button
                    type="button"
                    class="btn-flat"
                    @click.prevent="$emit('cancel')"
                    v-text="__(cancelText)"
                    v-if="cancellable"
                    :disabled="busy"
                />
                <button
                    class="ltr:ml-4 rtl:mr-4"
                    :class="buttonClass"
                    :disabled="disabled || busy"
                    v-text="__(buttonText)"
                />
            </div>
        </form>
    </modal>
</template>

<script>
export default {
    emits: ['opened', 'confirm', 'cancel'],
    props: {
        title: {
            type: String,
        },
        bodyText: {
            type: String,
        },
        buttonText: {
            type: String,
            default: 'Confirm',
        },
        cancellable: {
            type: Boolean,
            default: true,
        },
        cancelText: {
            type: String,
            default: 'Cancel',
        },
        danger: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        busy: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            escBinding: null,
        };
    },

    computed: {
        buttonClass() {
            return this.danger ? 'btn-danger' : 'btn-primary';
        },
    },

    methods: {
        dismiss() {
            if (this.busy) return;

            this.$emit('cancel');
        },
        submit() {
            if (this.busy) return;

            this.$emit('confirm');
        },
    },

    created() {
        this.escBinding = this.$keys.bind('esc', this.dismiss);
    },

    beforeUnmount() {
        this.escBinding.destroy();
    },
};
</script>
