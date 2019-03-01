<template>
    <modal name="confirmation-modal" :pivotY="0.1">
        <div class="flex flex-col h-full">
            <div class="text-lg font-medium p-3 pb-0">
                {{ title }}
            </div>
            <div class="flex-1 overflow-scrollf p-3">
                <slot>
                    <p>{{ __('Are you sure?') }}</p>
                </slot>
            </div>
            <div class="p-3 bg-grey-20 border-t flex items-center text-sm">
                <button class="btn" :class="buttonClass" v-text="buttonText" @click="$emit('confirm')" />
                <button class="ml-3 text-grey" @click="$emit('cancel')">{{ __('Cancel') }}</button>
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
        buttonText: {
            type: String,
            default: 'Confirm'
        },
        danger: {
            type: Boolean,
            default: false
        }
    },

    computed: {
        buttonClass() {
            return this.danger ? 'btn-danger' : 'btn-primary';
        }
    }
}
</script>
