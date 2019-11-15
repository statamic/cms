<template>
    <div class="checkboxes-fieldtype-wrapper"  :class="{'inline-mode': config.inline}">
        <div class="option" v-for="(option, $index) in options" :key="$index">
            <input type="checkbox"
                   :name="name + '[]'"
                   :id="name + $index"
                   :value="option.value"
                   :disabled="isReadOnly"
                   v-model="values"
            />
            <label :for="name + $index">{{ option.label || option.value }}</label>
        </div>
    </div>
</template>

<script>
import HasInputOptions from './HasInputOptions.js'

export default {

    mixins: [Fieldtype, HasInputOptions],

    data() {
        return {
            values: this.value || []
        }
    },

    computed: {
        options() {
            return this.normalizeInputOptions(this.config.options);
        }
    },

    watch: {

        values(values, oldValues) {
            values = this.sortValues(values);

            if (JSON.stringify(values) === JSON.stringify(oldValues)) return;

            this.update(values);
        },

        value(value) {
            this.values = this.sortValues(value);
        }

    },

    methods: {

        focus() {
            document.getElementById(`${this.name}-0`).focus();
        },

        getReplicatorPreviewText() {
            return this.values.map(item => {
                var option = _.findWhere(this.config.options, {value: item});
                return (option) ? option.text : item;
            }).join(', ');
        },

        sortValues(values) {
            if (!values) return [];

            return this.options
                .filter(opt => values.includes(opt.value))
                .map(opt => opt.value);
        }

    }
};
</script>
