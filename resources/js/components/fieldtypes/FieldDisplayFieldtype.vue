<template>
    <Input
        ref="input"
        :id="fieldId"
        :name="name"
        :value="value"
        @update:modelValue="updateDebounced"
        @keydown="$emit('keydown', $event)"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    >
        <template #append>
            <Button size="sm" :icon="hidden ? 'eye' : 'eye-closed'" variant="ghost" @click="toggleHidden" />
        </template>
    </Input>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Button, Input } from '@statamic/ui';

export default {
    mixins: [Fieldtype],

    components: { Button, Input },

    inject: ['getFieldSettingsValue', 'updateFieldSettingsValue'],

    computed: {
        hidden() {
            return this.getFieldSettingsValue('hide_display');
        },
    },

    mounted() {
        this.$nextTick(() => {
            this.$el.querySelector(`#${this.fieldId}`)?.select();
        });
    },

    methods: {
        toggleHidden() {
            this.updateFieldSettingsValue('hide_display', !this.hidden);
        },
    },
};
</script>
