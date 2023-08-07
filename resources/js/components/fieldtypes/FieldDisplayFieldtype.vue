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
                <svg-icon name="light/hidden" class="w-5 h-5" :class="{ 'text-gray-500': !hidden }" />
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

        hidden() {
            return this.$store.state.publish[this.storeName].values.hide_display;
        }

    },

    mounted() {
        this.$refs.input.select();
    },

    methods: {

        toggleHidden() {
            this.nearestPublishContainer.setFieldValue('hide_display', ! this.hidden);
        }

    }

}
</script>
