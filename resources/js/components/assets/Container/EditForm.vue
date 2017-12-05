<template>

    <div class="alert alert-danger" v-if="hasErrors">
        <ul>
            <li v-for="error in errors">{{ error }}</li>
        </ul>
    </div>

    <div class="flexy mb-24">
        <h1 class="fill" v-if="isNew">{{ translate('cp.creating_asset_container') }}</h1>
        <h1 class="fill" v-else>{{ translate('cp.editing_asset_container') }}</h1>
        <button type="submit" class="btn btn-primary" @click="save">{{ translate('cp.save') }}</button>
    </div>

    <div class="publish-fields card">

        <div class="form-group">
            <label class="block">{{ translate('cp.title') }}</label>
            <small class="help-block">{{ translate('cp.asset_name_instructions') }}</small>
            <input type="text" class="form-control" v-model="config.title" autofocus="autofocus" />
        </div>

        <div class="form-group" v-if="isNew">
            <label class="block">{{ translate('cp.handle') }}</label>
            <div class="help-block">{{ translate('cp.asset_handle_instructions') }}</div>
            <input type="text" class="form-control" v-model="config.handle" @keydown="isHandleModified = true" />
        </div>

        <div class="form-group">
            <label class="block">{{ translate('cp.fieldset') }}</label>
            <small class="help-block">{{ translate('cp.asset_fieldset_instructions') }}</small>
            <fieldset-fieldtype :data.sync="config.fieldset"></fieldset-fieldtype>
        </div>

        <div class="form-group">
            <label class="block">{{ translate('cp.driver') }}</label>
            <small class="help-block">{{ translate('cp.asset_driver_instructions') }}</small>
            <select-fieldtype :data.sync="config.driver" :options="drivers"></select-fieldtype>
        </div>

        <driver-local
            v-if="driver === 'local'"
            :editing="true"
            :path.sync="config.local.path"
            :url.sync="config.local.url">
        </driver-local>

        <driver-s3
            v-if="driver === 's3'"
            :editing="true"
            :key.sync="config.s3.key"
            :secret.sync="config.s3.secret"
            :bucket.sync="config.s3.bucket"
            :region.sync="config.s3.region"
            :path.sync="config.s3.path">
        </driver-s3>
    </div>

</template>


<script>
export default {

    components: {
        'driver-local': require('./Wizard/DriverLocal.vue'),
        'driver-s3': require('./Wizard/DriverS3.vue')
    },

    props: {
        isNew: Boolean,
        container: Object
    },

    data: function () {
        return {
            config: {
                title: null,
                handle: null,
                driver: 'local',
                fieldset: null,
                local: {},
                s3: {}
            },
            drivers: [
                { value: 'local', text: 'Local' },
                { value: 's3', text: 'Amazon S3' }
            ],
            isHandleModified: false,
            errors: []
        };
    },

    computed: {
        driver: function () {
            return this.config.driver;
        },
        hasErrors: function() {
            return _.size(this.errors) !== 0;
        }
    },

    ready: function () {
        if (! this.isNew) {
            var driver = this.container.driver || 'local';
            this.config.driver = driver;
            this.config.title = this.container.title;
            this.config.handle = this.container.handle;
            this.config.fieldset = this.container.fieldset;
            this.config[driver] = this.container;

        } else {
            // For new containers, set the region dropdown to the first option
            this.config.s3.region = _.first(this.s3Regions).value;
        }

        if (this.isNew) {
            this.syncTitleAndHandleFields();
        }
    },

    methods: {

        save: function () {
            var url = (this.isNew) ? cp_url('configure/content/assets') : cp_url('configure/content/assets/'+this.container.id);

            this.$http.post(url, this.config).success(function (response) {
                if (response.success) {
                    window.location = response.redirect;
                } else {
                    this.errors = response.errors;
                }
            });
        },

        syncTitleAndHandleFields: function() {
            this.$watch('config.title', function(title) {
                if (this.isHandleModified) return;

                this.config.handle = this.$slugify(title);
            });
        }

    }

}
</script>
