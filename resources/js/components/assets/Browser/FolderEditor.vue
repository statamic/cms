<template>

    <modal :show="true" class="asset-modal asset-folder-editor" :saving="saving" :loading="loading">
        <template slot="close">
            <button type="button" tabindex="-1" class="close" slot="close" aria-label="Close" @click="close"><span aria-hidden="true">&times;</span>
            </button>
        </template>

        <template slot="header">
            <h1 v-if="create">{{ translate('cp.create_folder') }}</h1>
            <h1 v-if="!create">{{ translate('cp.edit_folder') }}</h1>
        </template>

        <template slot="body">

            <div class="alert alert-danger" v-if="hasErrors">
                <p v-for="error in errors">{{ error }}</p>
            </div>

            <div class="form-group" v-if="create">
                <label class="block">{{ translate('cp.name') }}</label>
                <small class="help-block">{{ translate('cp.folder_directory_instructions') }}</small>
                <input type="text" class="form-control" v-model="form.basename" @keyup.esc="close" v-focus="create">
            </div>

            <div class="form-group">
                <label class="block">{{ translate('cp.title') }}</label>
                <small class="help-block">{{ translate('cp.folder_title_instructions') }}</small>
                <input type="text" class="form-control" v-model="form.title" @keyup.esc="close" v-focus="! create">
            </div>

        </template>

        <template slot="footer">
            <button type="button" class="btn" @click="close">{{ translate('cp.close') }}</button>
            <button type="button" class="btn btn-primary btn-small" @click="save">{{ translate('cp.save') }}</button>
        </template>
    </modal>

</template>

<script>
export default {

    props: {
        container: String,
        path: String,
        create: Boolean
    },

    data: function() {
        return {
            form: {},
            folder: {},
            loading: true,
            saving: false,
            errors: [],
            basenameModified: false
        }
    },

    computed: {

        hasErrors() {
            return Object.keys(this.errors).length > 0 && !this.saving;
        }

    },

    methods: {

        reset: function() {
            this.path = '';
            this.folder = {};
            this.form = {};
            this.loading = true;
        },

        getFolder: function() {
            if (this.create) {
                this.getBlankFolder();
            } else {
                this.getExistingFolder();
            }
        },

        getBlankFolder: function() {
            this.folder = {};
            this.form = {
                container: this.container,
                parent: this.path,
                title: '',
                basename: ''
            };
            this.loading = false;
        },

        getExistingFolder: function() {
            var url = cp_url('assets/folders/' + this.container + '/' + this.path);

            this.$http.get(url).success(function(data) {
                this.folder = data;
                this.form = {
                    title: data.title
                };
                this.loading = false;
            });
        },

        save: function() {
            this.saving = true;

            if (this.create) {
                this.saveNewFolder();
            } else {
                this.saveExistingFolder();
            }
        },

        saveNewFolder: function() {
            var url = cp_url('assets/folders');

            this.$http.post(url, this.form).success(function(data) {
                this.$emit('created', data.folder.path);
                this.saving = false;
                this.close();
            }).error(function(data) {
                this.errors = data;
                this.saving = false;
            });
        },

        saveExistingFolder: function() {
            var url = cp_url('assets/folders/' + this.container + '/' + this.path);

            this.$http.post(url, this.form).success(function(data) {
                this.$emit('updated');
                this.saving = false
                this.close();
            });
        },

        close: function() {
            this.$emit('closed');
        }

    },

    ready: function() {
        this.getFolder();
    }

}
</script>
