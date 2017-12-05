<template>
    <div class="asset-folder-fieldtype-wrapper">
        <small class="help-block" v-if="!container">{{ translate('cp.select_asset_container') }}</small>
        <div v-if="container && loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>
        <select-fieldtype v-if="container && !loading" :name="name" :data.sync="data" :config="selectConfig"></select-fieldtype>
    </div>
</template>

<script>
module.exports = {

    mixins: [Fieldtype],

    data: function() {
        return {
            loading: true,
            options: {},
            container: null,
            autoBindChangeWatcher: false
        }
    },

    computed: {
        selectConfig: function() {
            return {
                options: this.options
            };
        },

        allowBlank: function() {
            return false;
        }
    },

    methods: {

        /**
         * This fieldtype can be used as a sibling to a container field.
         * It will refresh itself when the container field value changes.
         */
        bootstrapForContainerField() {
            var self = this;

            // When the asset container is modified, we want to either get the appropriate folders or reset the folders.
            this.$parent.$watch('field', function (field) {
                // Other changes in the field will trigger this. We want to
                // ignore everything except a modifier asset container value
                if (field.container === self.container) {
                    return false;
                }

                if (field.container) {
                    self.loading = true;
                    self.container = field.container;
                    self.getFolders();
                } else {
                    self.container = null;
                    self.data = null;
                }
            }, { deep: true });

            if (this.$parent.field.container) {
                this.container = this.$parent.field.container;
                this.getFolders();
            }
        },

        getFolders: function() {
            this.$http.get(cp_url('assets/containers/' + this.container + '/folders'), function(data) {
                var options = (this.allowBlank) ? [{ value: null, text: '', }] : [];

                _.each(data, function (folder) {
                    const text = (folder.path === folder.title)
                        ? folder.path
                        : folder.path + ' (' + folder.title + ')';

                    options.push({
                        value: folder.path,
                        text: text
                    });
                });

                this.options = options;
                this.loading = false;

                if (!this.data) {
                    this.data = options[0].value;
                }

                this.bindChangeWatcher();
            });
        }
    },

    ready: function() {
        // If a container prop has been provided, we simply need to get the folders.
        // Otherwise, bootstrap this field so it will work with a sibling container field.
        if (this.config.container) {
            this.container = this.config.container;
            this.getFolders();
        } else {
            this.bootstrapForContainerField();
        }
    }

};
</script>
