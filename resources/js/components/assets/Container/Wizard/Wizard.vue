<template>

    <div>

        <h1 class="mb-3">{{ translate('cp.creating_asset_container') }}</h1>

        <div class="publish-form flush card">

            <div class="breadcrumbs wizard">
                <span class="step" :class="{'active': step === 'meta'}">
                    Meta
                </span>
                <span class="step" :class="{'active': step === 'driver'}">
                    Driver
                </span>
                <span class="step" :class="{'active': step === 'fieldset'}">
                    Fieldset
                </span>
            </div>

            <div class="pad">
                <meta-fields
                    v-if="step === 'meta'"
                    :title.sync="title"
                    :handle.sync="handle"
                    @submit="metaSubmit">
                </meta-fields>

                <driver-fields
                    v-if="step === 'driver'"
                    :driver.sync="driver"
                    :config.sync="driverConfig"
                    @submit="driverSubmit">
                </driver-fields>

                <fieldset-fields
                    v-if="step === 'fieldset'"
                    :fieldset.sync="fieldset"
                    @submit="fieldsetSubmit">
                </fieldset-fields>
            </div>

        </div>

    </div>

</template>


<script>
export default {

    components: {
        'meta-fields': require('./MetaFields.vue'),
        'driver-fields': require('./DriverFields.vue'),
        'fieldset-fields': require('./FieldsetFields.vue'),
    },


    data() {
        return {
            step: 'meta',
            title: null,
            handle: null,
            fieldset: null,
            driver: null,
            driverConfig: {}
        }
    },


    methods: {

        metaSubmit() {
            this.step = 'driver';
        },

        driverSubmit() {
            this.step = 'fieldset';
        },

        fieldsetSubmit() {
            this.complete();
        },

        complete() {
            const url = cp_url('configure/content/assets');

            let payload = {
                title: this.title,
                handle: this.handle,
                fieldset: this.fieldset,
                driver: this.driver,
                local: this.driverConfig.local,
                s3: this.driverConfig.s3
            };

            this.$http.post(url, payload).success(function (response) {
                if (response.success) {
                    window.location = response.redirect;
                } else {
                    this.errors = response.errors;
                }
            });
        }

    }

}
</script>
