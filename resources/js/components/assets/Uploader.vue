<script>
export default {

    render(h) {
        const fileField = h('input', {
            class: { hidden: true },
            attrs: { type: 'file', multiple: true },
            ref: 'nativeFileField'
        });

        return h('div', {}, [
            fileField,
            ...this.$scopedSlots.default({})
        ]);
    },


    props: ['container', 'path'],


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
                _token: Statamic.csrfToken
            };
        }

    },


    mounted() {
        this.bindUploader();
    },


    // Using beforeDestroy instead of destroy, since the destroy hook didn't seem to
    // get called at all sometimes when using `npm run production`. Works fine when
    // using `npm run dev`. beforeDestroy works fine in both cases. ¯\_(ツ)_/¯
    beforeDestroy() {
        $(this.$el).unbind().removeData();
    },


    watch: {

        uploads(uploads) {
            this.$emit('updated', uploads);
        },

        extraData(data) {
            $(this.$el).data('dmUploader').settings.extraData = data;
        },

    },


    methods: {

        /**
         * Open the native file browser
         */
        browse() {
            $(this.$refs.nativeFileField).click();
        },

        /**
         * Bind the uploader plugin to the DOM
         */
        bindUploader() {
            $(this.$el).dmUploader({
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
                    this.$emit('upload-complete', response, this.uploads);

                    let index = _(this.uploads).findIndex({ id });
                    this.uploads.splice(index, 1);
                },

                onUploadError: (id, errMsg, response) => {
                    let upload = _(this.uploads).findWhere({ id });

                    if (response.responseJSON) {
                        errMsg = response.responseJSON.message;
                    }

                    upload.errorMessage = errMsg;

                    this.$emit('error', upload, this.uploads);
                }
            });
        }

    }


}
</script>
