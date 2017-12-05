<template>

    <div class="asset-uploader">
        <input type="file" multiple="multiple" class="hide" v-el:native-file-field>
    </div>

</template>


<script>
export default {


    props: ['domElement', 'container', 'path'],


    data() {
        return {
            uploads: []
        }
    },


    computed: {

        extraData() {
            return {
                container: this.container,
                folder: this.path,
                _token: document.querySelector('#csrf-token').getAttribute('value')
            };
        }

    },


    ready() {
        this.bindUploader();
    },


    destroyed() {
        $(this.domElement).unbind().removeData();
    },


    watch: {

        uploads(uploads) {
            this.$emit('updated', uploads);
        },

        container() {
            this.updateExtraData();
        },

        path() {
            this.updateExtraData();
        }

    },


    methods: {

        /**
         * Open the native file browser
         */
        browse() {
            $(this.$els.nativeFileField).click();
        },

        /**
         * Bind the uploader plugin to the DOM
         */
        bindUploader() {
            $(this.domElement).dmUploader({
                url: cp_url('assets'),

                extraData: this.extraData,

                onNewFile: (id, file) => {
                    this.uploads.push({
                        id: id,
                        basename: file.name,
                        extension: file.name.split('.').pop(),
                        percent: 0,
                        errorMessage: null
                    });
                },

                onUploadProgress: (id, percent) => {
                    let upload = _(this.uploads).findWhere({ id });
                    upload.percent = percent;
                    this.$emit('progress', upload, this.uploads);
                },

                onUploadSuccess: (id, response) => {
                    this.$emit('upload-complete', response.asset, this.uploads);

                    let index = _(this.uploads).findIndex({ id });
                    this.uploads.splice(index, 1);
                },

                onComplete: () => {
                    this.$emit('uploads-complete', this.uploads);
                },

                onUploadError: (id, errMsg, response) => {
                    let upload = _(this.uploads).findWhere({ id });

                    if (response.status == 400) {
                        errMsg = response.responseJSON;
                    } else if (response.status == 413) {
                        errMsg = "This file exceeds your server's max upload filesize limit.";
                    }

                    upload.errorMessage = errMsg;

                    this.$emit('error', upload, this.uploads);
                }
            });
        },

        /**
         * Update the "extraData" object the plugin will use when uploading.
         */
        updateExtraData() {
            $(this.domElement).data('dmUploader').settings.extraData = this.extraData;
        }

    }


}
</script>
