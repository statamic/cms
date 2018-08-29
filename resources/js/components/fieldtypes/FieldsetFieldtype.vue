<template>
    <div class="template-fieldtype-wrapper">

        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>

        <div v-if="!loading">

            <div class="input-group" v-show="creating">
                <input type="text"
                       ref="create-field"
                       class="form-control"
                       v-model="newFieldsetName"
                       @keydown.enter.prevent="create"
                       @keydown.esc="cancelAdd"
                />
                <div class="input-group-btn">
                    <button class="btn btn-primary" @click.prevent="create" :disabled="storePending">{{ translate('cp.create') }}</button>
                    <button class="btn btn-default" @click.prevent="cancelAdd">{{ translate('cp.cancel') }}</button>
                </div>
            </div>

            <div class="input-group" v-show="!creating">
                <select-fieldtype :name="name" :data.sync="data" :config="selectConfig"></select-fieldtype>

                <span class="input-group-btn">
                    <button class="btn" @click.prevent="add" v-if="canAdd">
                        <span class="icon icon-plus"></span>
                    </button>
                    <button class="btn" @click.prevent="refresh">
                        <span class="icon icon-cycle"></span>
                    </button>
                </span>
            </div>

        </div>

    </div>
</template>

<script>

export default {

    mixins: [Fieldtype],

    props: {
        required: Boolean,
        url: String,
    },

    data: function() {
        return {
            loading: true,
            creating: false,
            storePending: false,
            newFieldsetName: '',
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

        canAdd() {
            return Vue.can('super');
        }
    },

    mounted() {
        this.getFieldsets();
    },

    methods: {

        add() {
            this.creating = true;
            this.$nextTick(() => this.$refs.createField.focus());
        },

        cancelAdd() {
            this.creating = false;
            this.newFieldsetName = '';
        },

        create() {
            if (this.storePending) return;

            this.storePending = true;

            this.$http.post(cp_url('fieldsets/quick'), { name: this.newFieldsetName }).success((response) => {
                this.data = this.newFieldsetName;
                this.storePending = false;
                this.cancelAdd();
                this.refresh();
            });
        },

        refresh() {
            this.loading = true;
            this.getFieldsets();
        },

        getFieldsets() {
            var url = cp_url('fieldsets-json');
            var params = {};

            if (this.url) {
                // Append the URL if we want to get available fieldsets for a particular page.
                params.url = this.url;
            }

            if (this.config && ! this.config.hidden) {
                // By default we don't get hidden fieldsets.
                // You can specify hidden: true to get them.
                params.hidden = false;
            }

            url += '?' + $.param(params);

            this.$http.get(url, function(data) {
                // If a value is required, don't add a blank row.
                var options = (this.required) ? [] : [{ value: null, text: '' }];

                _.each(data.items, function(fieldset) {
                    options.push({
                        value: fieldset.uuid,
                        text: fieldset.title
                    });
                });
                this.options = options;
                this.loading = false;

                // If a value is required and we don't already have a value, select the first one.
                if (this.required && !this.data) {
                    this.data = this.options[0].value;
                }

                this.bindChangeWatcher();
            });
        }

    }
};
</script>
