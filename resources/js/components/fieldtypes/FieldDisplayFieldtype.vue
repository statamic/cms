<template>
    <div class="flex items-center">
        <div class="input-group">
            <input
                ref="input"
                class="input-text"
                :id="fieldId"
                :name="name"
                :value="modelValue"
                type="text"
                @input="updateDebounced($event.target.value)"
                @keydown="$emit('keydown', $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
            >

            <button
                class="input-group-append flex items-center"
                v-tooltip="hideDisplay ? __('Hidden') : __('Visible')"
                @click="toggleHidden"
            >
                <svg-icon v-show="hideDisplay" name="light/hidden" class="w-5 h-5 text-gray-600 dark:text-dark-200" />
                <svg-icon v-show="!hideDisplay" name="light/eye" class="w-5 h-5" />
            </button>
        </div>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    emits: ['focus', 'blur'],

    mixins: [Fieldtype],

    inject: ['storeName'],

    mounted() {
        this.$refs.input.select();
    },

    computed: {
        hideDisplay: {
            get() {
                return this.$store.state.publish[this.storeName].values.hide_display
            },
            set(newValue) {
                return this.$store.commit(`publish/${this.storeName}/setFieldValue`, {
                    handle: 'hide_display',
                    value: newValue,
                })
            }
        }
    },

    methods: {
        toggleHidden() {
            this.hideDisplay = ! this.hideDisplay
        }
    }
}
</script>
