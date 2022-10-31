<script>
require('dmuploader')

export default {

    render(h) {
        const fileField = h('input', {
            class: { hidden: true },
            attrs: { type: 'file', multiple: true },
            ref: 'nativeFileField'
        });

        return h('div', { on: {
            'dragenter': this.dragenter,
            'dragleave': this.dragleave,
            'drop': this.drop,
        }}, [
            h('div', { class: { 'pointer-events-none': this.dragging }}, [
                fileField,
                ...this.$scopedSlots.default({ dragging: this.enabled ? this.dragging : false })
            ])
        ]);
    },


    props: {
        enabled: {
            type: Boolean,
            default: () => true
        },
        container: String,
        path: String,
        url: { type: String, default: () => cp_url('assets') }
    },


    data() {
        return {
            dragging: false,
            uploads: []
        }
    },


    computed: {

        extraData() {
            return {
                container: this.container,
                folder: this.path,
                _token: Statamic.$config.get('csrfToken')
            };
        }

    },


    mounted() {
        if (this.enabled) {
            this.bindUploader();
        }
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
                url: this.url,

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
                    this.$emit('upload-complete', response.data, this.uploads);

                    let index = _(this.uploads).findIndex({ id });
                    this.uploads.splice(index, 1);
                },

                onUploadError: (id, errMsg, response) => {
                    let upload = _(this.uploads).findWhere({ id });

                    if (response.responseJSON) {
                        errMsg = response.responseJSON.message;
                    } 

                    if (! errMsg) {
                        if (response.status === 413) {
                            errMsg = __('Upload failed. The file is larger than is allowed by your server.');
                        } else {
                            errMsg = __('Upload failed. The file might be larger than is allowed by your server.');
                        }
                    }

                    upload.errorMessage = errMsg;

                    this.$emit('error', upload, this.uploads);
                }
            });
        },

        dragenter(e) {
            this.dragging = true;
        },

        dragleave(e) {
            // When dragging over a child, the parent will trigger a dragleave.
            if (e.target !== e.currentTarget) return;

            this.dragging = false;
        },

        drop(e) {
            this.dragging = false;
        }
    }

}
</script>
