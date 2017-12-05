<template>

    <div class="publish-fields">

        <div class="form-group">
            <label class="block">{{ translate('cp.driver') }}</label>
            <small class="help-block">{{ translate('cp.asset_driver_instructions') }}</small>

            <ul class="list-unstyled">
                <li>
                    <input type="radio" v-model="driver" value="local" id="driver-local" />
                    <label for="driver-local">Local Filesystem</label>
                </li>
                <li>
                    <input type="radio" v-model="driver" value="s3" id="driver-s3" />
                    <label for="driver-s3">Amazon S3</label>
                </li>
            </ul>
        </div>

        <driver-local
            v-if="driver === 'local'"
            :path.sync="config.local.path"
            :url.sync="config.local.url"
            @submit="submit">
        </driver-local>

        <driver-s3
            v-if="driver === 's3'"
            :key.sync="config.s3.key"
            :secret.sync="config.s3.secret"
            :bucket.sync="config.s3.bucket"
            :region.sync="config.s3.region"
            :path.sync="config.s3.path"
            @submit="submit">
        </driver-s3>

    </div>

</template>

<script>
    export default {

        components: {
            'driver-local': require('./DriverLocal.vue'),
            'driver-s3': require('./DriverS3.vue')
        },


        props: {
            driver: String,
            config: Object
        },


        data() {
            return {
                //
            }
        },


        methods: {

            submit() {
                this.$emit('submit');
            }

        }

    }
</script>
