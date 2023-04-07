<template>
    <modal name="confirmation-modal" @opened="$emit('opened')">
        <div class="confirmation-modal flex flex-col h-full">
            <header v-if="title" class="text-lg font-semibold px-5 py-3 bg-gray-200 rounded-t-lg flex items-center justify-between border-b">
                {{ __(title) }}
            </header>
            <div class="flex-1 px-5 py-6 text-gray">
                <p v-if="bodyText" v-text="bodyText" />
                <slot v-else>
                    <p>{{ __('Are you sure?') }}</p>
                </slot>
            </div>
            <div class="px-5 py-3 bg-gray-200 rounded-b-lg border-t flex items-center justify-end text-sm">
                <button class="text-gray hover:text-gray-900" @click="$emit('cancel')" v-text="__(cancelText)" v-if="cancellable" />
                <button class="ml-4" :class="buttonClass" v-text="buttonText" @click="$emit('confirm')" />
            </div>
        </div>
    </modal>
</template>

<script>
export default {
    props: {
        title: {
            type: String
        },
        bodyText: {
            type: String
        },
        buttonText: {
            type: String,
            default: 'Confirm'
        },
        cancellable: {
            type: Boolean,
            default: true
        },
        cancelText: {
            type: String,
            default: 'Cancel'
        },
        danger: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            escBinding: null,
            enterBinding: null,
        }
    },

    computed: {
        buttonClass() {
            return this.danger ? 'btn-danger' : 'btn-primary';
        }
    },

    methods: {
        dismiss() {
            this.$emit('cancel')
        },
        submit() {
            this.$emit('confirm')
        }
    },

    created() {
        this.escBinding = this.$keys.bind('esc', this.dismiss)
        this.enterBinding = this.$keys.bind('enter', this.submit)
    },

     beforeDestroy() {
        this.escBinding.destroy()
        this.enterBinding.destroy()
    },
}
</script>
