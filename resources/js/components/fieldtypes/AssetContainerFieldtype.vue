<template>
    <div class="asset-container-fieldtype-wrapper">
        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>

        <select-fieldtype v-if="!loading" :name="name" :data.sync="data" :config="selectConfig"></select-fieldtype>
    </div>
</template>

<script>
module.exports = {

    mixins: [Fieldtype],

    data: function() {
        return {
            loading: true,
            options: {},
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
            return this.config && this.config.allow_blank;
        }
    },

    ready: function() {
        this.$http.get(cp_url('assets/containers/get'), function(data) {
            var options = (this.allowBlank) ? [{ value: null, text: '', }] : [];

            _.each(data.items, function(container) {
                options.push({
                    value: container.id,
                    text: container.title
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

};

</script>
