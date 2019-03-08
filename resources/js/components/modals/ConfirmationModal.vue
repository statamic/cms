<template>
    <modal name="confirmation-modal" :pivotY="0.1">
        <div class="flex flex-col h-full text-center">
            <div class="text-lg font-medium p-4 pb-0">
                {{ title }}
            </div>
            <div class="flex-1 overflow-scroll p-3 text-grey">
                <p v-if="bodyText" v-text="bodyText" />
                <slot v-else>
                    <p>{{ __('Are you sure?') }}</p>
                </slot>
            </div>
            <div class="p-2 bg-grey-20 border-t flex items-center justify-center text-sm">
                <button class="text-grey hover:text-grey-90 mx-1" @click="$emit('cancel')">{{ __('Cancel') }}</button>
                <button class="btn mx-1" :class="buttonClass" v-text="buttonText" @click="$emit('confirm')" />
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
