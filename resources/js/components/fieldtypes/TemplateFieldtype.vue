<template>
    <div class="template-fieldtype-container">
        <v-select
            ref="input"
            :name="name"
            @input="update"
            :clearable="config.clearable"
            :disabled="isReadOnly"
            :options="options"
            :reduce="selection => selection.value"
            :searchable="true"
            :push-tags="false"
            :multiple="false"
            :value="value" />
    </div>
</template>

<script>
    export default {
        mixins: [Fieldtype],

        data: function() {
            return {
                loading: true,
                options: []
            };
        },

        mounted() {
            this.$axios.get(cp_url("api/templates")).then(response => {
                var templates = response.data;

                // Filter out partials
                if (this.config.hide_partials) {
                    templates = _.reject(templates, function(template) {
                        return template.match(/(^_.*|\/_.*|\._.*)/g);
                    });
                }

                // Set default
                var options = [];

                if (!this.config.hide_default) {
                    options.push({
                        label: __("Inherit (Default)"),
                        value: null
                    });
                }

                _.each(templates, function(template) {
                    options.push({
                        label: template,
                        value: template
                    });
                });

                this.options = options;
                this.loading = false;
            });
        }
    };
</script>
