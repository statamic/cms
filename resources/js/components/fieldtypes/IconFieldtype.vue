<template>
    <div class="flex">
        <v-select
            ref="input"
            :name="name"
            :clearable="false"
            :disabled="config.disabled || isReadOnly"
            :options="options"
            :placeholder="config.placeholder"
            :searchable="false"
            :multiple="false"
            :close-on-select="true"
            :value="this.value"
            :create-option="(value) => ({ value, label: value })"
            @input="vueSelectUpdated"
            @search:focus="$emit('focus')"
            @search:blur="$emit('blur')">
            <template slot="option" slot-scope="option">
                <div class="flex items-center">
                    <svg-icon v-if="!option.html" :name="`${meta.set}/${option.label}`" class="w-5 h-5" />
                    <div v-if="option.html" v-html="option.html" class="w-4 h-4" />
                    <span class="text-xs ml-4 text-gray-700 truncate">{{ option.label }}</span>
                </div>
            </template>
            <template slot="selected-option" slot-scope="option">
                <svg-icon v-if="!option.html" :name="`${meta.set}/${option.label}`" class="w-5 h-5" />
                <div v-if="option.html" v-html="option.html" class="w-5 h-5" />
            </template>
        </v-select>
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    computed: {
          options() {
            let options = [];
            for (let [name, html] of Object.entries(this.meta.icons)) {
                options.push({
                    value: name,
                    label: name,
                    html
                });
            }
            return options;
        }
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        },

        vueSelectUpdated(value) {
            if (value) {
                this.update(value.value)
            } else {
                this.update(null);
            }
        }
    }
};
</script>
