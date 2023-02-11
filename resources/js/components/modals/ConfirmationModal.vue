<template>
    <modal name="confirmation-modal" :pivotY="0.1" :overflow="false">
        <div class="confirmation-modal flex flex-col h-full">
            <div class="text-lg font-medium p-4 pb-0">
                {{ __(title) }}
            </div>
            <div class="flex-1 px-4 py-6 text-gray">
                <p v-if="bodyText" v-text="bodyText" />
                <slot v-else>
                    <p>{{ __('Are you sure?') }}</p>
                </slot>
            </div>
            <div class="p-4 bg-gray-200 border-t flex items-center justify-end text-sm">
                <button class="text-gray hover:text-gray-900" @click="$emit('cancel')" v-text="__(cancelText)" />
                <button class="ml-4" :class="buttonClass" v-text="buttonText" @click="$emit('confirm')" />
            </div>
        </div>
    </modal>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            required: true
        },
        bodyText: {
            type: String
        },
        buttonText: {
            type: String,
            default: 'Confirm'
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
