<template>


    <div class="flex items-center">
        <div class="input-group">
            <input
                ref="input"
                class="input-text"
                :id="fieldId"
                :name="name"
                :value="value"
                type="text"
                @input="updateDebounced($event.target.value)"
                @keydown="$emit('keydown', $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
            >
            <button
                class="input-group-append flex items-center"
                v-tooltip="hidden ? __('Hidden') : __('Visible')"
                @click="toggleHidden"
            >
                <svg-icon v-show="hidden" name="light/hidden" class="w-5 h-5 text-gray-600 dark:text-dark-200" />
                <svg-icon v-show="!hidden" name="light/eye" class="w-5 h-5 " />
            </button>
        </div>
    </div>

</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype],

    inject: [
        'getFieldSettingsValue',
        'updateFieldSettingsValue',
    ],

    computed: {

        hidden() {
            return this.getFieldSettingsValue('hide_display');
        }

    },

    mounted() {
        this.$refs.input.select();
    },

    methods: {

        toggleHidden() {
            this.updateFieldSettingsValue('hide_display', ! this.hidden)
        }

    }

}
</script>
