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

    inject: ['storeName'],

    computed: {

        nearestPublishContainer() {
            let parent = this;
            while (parent.$options.name !== 'publish-container') {
                parent = parent.$parent;
                if (parent === this.$root) return null;
            }
            return parent;
        },

        nearestFieldSettings() {
            let parent = this;
            while (parent.$options._componentTag !== 'field-settings') {
                parent = parent.$parent;
                if (parent === this.$root) return null;
            }
            return parent;
        },

        hidden() {
            return this.nearestFieldSettings.values.hide_display;
        }

    },

    mounted() {
        this.$refs.input.select();
    },

    methods: {

        toggleHidden() {
            this.nearestFieldSettings.updateField('hide_display', ! this.hidden)
        }

    }

}
</script>
