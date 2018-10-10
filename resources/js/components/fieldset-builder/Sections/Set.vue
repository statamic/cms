<template>

    <div :class="[ 'field-set', `field-set--${parentKey}` ]">
        <div :class="[ 'set-drag-handle', `set-drag-handle--${parentKey}` ]"></div>
        <span class="set-display-sizer" ref="set-display-sizer" v-text="set.display || set.name"></span>
        <span class="set-name-sizer" ref="set-name-sizer" v-text="set.name"></span>
        <input type="text" class="set-display mr-8" ref="display" v-model="set.display" :placeholder="set.display || set.name" :style="{width: displayFieldWidth}"  />
        <input type="text" class="set-name" v-model="set.name" :placeholder="set.name" :style="{width: nameFieldWidth}" @input="handleModified = true" />
        <input type="text" class="set-instructions" v-model="set.instructions" :placeholder="`${translate('cp.instructions')}...`" />
        <fieldset-fields
            v-ref=fields
            :fields.sync="set.fields"
            :section="section"
            :parent-key="setKey"
            :fieldtypes="fieldtypes"
            :is-adding="isAddingField"
            classes="field-fields set-fields"
            @selector-closed="fieldSelectorClosed"
        ></fieldset-fields>
        <div class="flex items-center mt-2">
            <a class="btn btn-default btn-small" @click="isAddingField = true">{{ translate('cp.add_field') }}</a>
            <a class="opacity-50 text-2xs hover:opacity-100 ml-1" @click="$emit('deleted', setIndex)">{{ translate('cp.delete_set') }}</a>
        </div>
    </div>

</template>

<script>
import { Sortable } from '@shopify/draggable';

export default {

    props: ['set', 'setIndex', 'fieldtypes', 'section', 'parentKey'],

    data() {
        return {
            isAddingField: false,
            handleModified: false,
            displayFieldWidth: '100%',
            nameFieldWidth: '100%'
        }
    },

    computed: {

        setKey() {
            return `${this.parentKey}-${this.set.id}`;
        },

        display() {
            return this.set.display;
        },

        name() {
            return this.set.name;
        },

    },

    watch: {

        display(val) {
            if (!this.handleModified) {
                this.set.name = this.$slugify(val, '_');
            }

            this.$nextTick(() => this.updateSetFieldWidths());
        },

        name(val) {
            this.updateSetFieldWidths();
        }

    },

    mounted() {
        this.handleModified = !this.set.isNew;
        this.updateFieldWidths();
    },

    methods: {

        updateSetFieldWidths() {
            this.displayFieldWidth = this.$refs.setDisplaySizer.offsetWidth + 'px';
            this.nameFieldWidth = this.$refs.setNameSizer.offsetWidth + 'px';
        },

        updateFieldWidths() {
            this.updateSetFieldWidths();
            this.$refs.fields.updateFieldWidths();
        },

        focus() {
            this.$refs.display.select();
        },

        fieldSelectorClosed() {
            this.isAddingField = false;
        },

    }

}
</script>
