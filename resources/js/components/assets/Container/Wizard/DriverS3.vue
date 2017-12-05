<template>

    <div class="form-group">
        <label class="block">{{ translate('cp.access_key_id') }}</label>
        <input type="text" class="form-control" v-model="key" />
    </div>

    <div class="form-group">
        <label class="block">{{ translate('cp.secret_access_key') }}</label>
        <input type="text" class="form-control" v-model="secret" />
    </div>

    <div class="form-group">
        <label class="block">{{ translate('cp.bucket') }}</label>
        <input type="text" class="form-control" v-model="bucket" />
    </div>

    <div class="form-group">
        <label class="block">{{ translate('cp.region') }}</label>
        <select-fieldtype :data.sync="region" :options="s3Regions"></select-fieldtype>
    </div>

    <div class="form-group">
        <label class="block">{{ translate('cp.path') }}</label>
        <input type="text" class="form-control" v-model="path" />
    </div>

    <div class="form-group">
        <button class="btn btn-default"
                @click="validateCredentials"
                :disabled="!hasCredentials || validating">
            Validate Credentials
        </button>

        <button class="btn btn-default"
                v-if="validationSuccess && !editing"
                @click="submit">
            Next Step
        </button>
    </div>

    <div class="form-group" v-if="validating || validationError || validationSuccess">
        <dl>

            <dd v-if="validating">
                <span class="icon icon-circular-graph animation-spin"></span>
                Validating...
            </dd>

            <dd class="text-danger" v-if="validationError && !validating">
                <span class="icon icon-cross"></span>
                Invalid Credentials <br>
                <small>{{ validationError }}</small>
            </dd>

            <dd v-if="validationSuccess && !validating">
                <span class="text-success">
                    <span class="icon icon-check"></span>
                    Valid credentials.
                </span><br>
                <small>Files detected: {{ filesDetected }}</small>
            </dd>

        </dl>
    </div>

</template>

<script>
    export default {

        props: {
            key: String,
            secret: String,
            bucket: String,
            region: String,
            path: String,
            editing: {
                type: Boolean,
                default() {
                    return false;
                }
            }
        },


        data() {
            return {
                validating: false,
                validationError: null,
                validationSuccess: false,
                filesDetected: null
            }
        },


        computed: {
            s3Regions: function () {
                return [
                    { value: 'us-east-1', text: 'US East (N. Virginia) / US Standard / us-east-1' },
                    { value: 'us-east-2', text: 'US East (Ohio) / us-east-2' },
                    { value: 'us-west-1', text: 'US West (N. California) / us-west-1' },
                    { value: 'us-west-2', text: 'US West (Oregon) / us-west-2' },
                    { value: 'ca-central-1', text: 'Canada (Central) / ca-central-1' },
                    { value: 'ap-south-1', text: 'Asia Pacific (Mumbai) / ap-south-1' },
                    { value: 'ap-northeast-2', text: 'Asia Pacific (Seoul) / ap-northeast-2' },
                    { value: 'ap-southeast-1', text: 'Asia Pacific (Singapore) / ap-southeast-1' },
                    { value: 'ap-southeast-2', text: 'Asia Pacific (Sydney) / ap-southeast-2' },
                    { value: 'ap-northeast-1', text: 'Asia Pacific (Tokyo) / ap-northeast-1' },
                    { value: 'eu-central-1', text: 'EU (Frankfurt) / eu-central-1' },
                    { value: 'eu-west-1', text: 'EU (Ireland) / eu-west-1' },
                    { value: 'eu-west-2', text: 'EU (London) / eu-west-2' },
                    { value: 'sa-east-1', text: 'South America (Sao Paulo) / sa-east-1)' }
                ]
            },

            hasCredentials() {
                return this.key && this.secret && this.region && this.bucket;
            }
        },


        ready() {
            this.setDefaults();
        },


        methods: {

            setDefaults() {
                this.key = this.key || '';
                this.secret = this.secret || '';
                this.bucket = this.bucket || '';
                this.path = this.path || '';
                this.region = this.region || 'us-east-1';
            },

            validateCredentials() {
                this.validationError = null;
                this.validating = true;

                this.$http.post(cp_url('assets/containers/validate-s3'), {
                    key: this.key,
                    secret: this.secret,
                    bucket: this.bucket,
                    region: this.region,
                    path: this.path,
                }).success(function (response) {
                    this.validationSuccess = true;
                    this.filesDetected = response.files;
                    this.validationError = null;
                    this.validating = false;
                }).error(function (response) {
                    this.validationSuccess = false;
                    this.validationError = response.error;
                    this.validating = false;
                });
            },

            submit() {
                this.$emit('submit');
            }

        }

    }
</script>
