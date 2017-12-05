<template>
    <div class="theme-fieldtype-wrapper">
        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>
        <select-fieldtype v-if="!loading" :name="name" :data.sync="data" :config="selectConfig"></select-fieldtype>
    </div>
</template>

<script>
module.exports = {

    props: ['data', 'config', 'name'],

    data: function() {
        return {
            loading: true,
            options: {}
        }
    },

    computed: {
        selectConfig: function() {
            return {
                options: this.options
            };
        }
    },

    ready: function() {
        this.$http.get(cp_url('system/themes/get'), function(data) {
            var options = [];
            _.each(data, function(theme) {
                options.push({
                    value: theme.folder,
                    text: theme.name
                });
            });
            this.options = options;
            this.loading = false;
        });
    }

};
</script>
