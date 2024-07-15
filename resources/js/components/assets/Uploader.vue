<script>
import { h } from 'vue';
import { Upload } from 'upload';
import uniqid from 'uniqid';

export default {
    expose: ['browse'],

    render() {
        const fileField = h('input', {
            class: { hidden: true },
            type: 'file',
            multiple: true,
            ref: 'nativeFileField',
        });

        return h('div', {
            onDragenter: this.dragenter,
            onDragover: this.dragover,
            onDragleave: this.dragleave,
            onDrop: this.drop,
        }, [
            h('div', { class: { 'pointer-events-none': this.dragging } }, [
                fileField,
                ...this.$slots.default({ dragging: this.enabled ? this.dragging : false })
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
        };
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
        this.$refs.nativeFileField.addEventListener('change', this.addNativeFileFieldSelections);
    },

    beforeUnmount() {
        this.$refs.nativeFileField.removeEventListener('change', this.addNativeFileFieldSelections);
    },

    watch: {
        uploads(uploads) {
            this.$emit('updated', uploads);
            this.processUploadQueue();
        }
    },

    methods: {
        browse() {
            this.$refs.nativeFileField.click();
        },

        addNativeFileFieldSelections(e) {
            for (let i = 0; i < e.target.files.length; i++) {
                this.addFile(e.target.files[i]);
            }
        },

        dragenter(e) {
            e.stopPropagation();
            e.preventDefault();
            this.dragging = true;
        },

        dragover(e) {
            e.stopPropagation();
            e.preventDefault();
        },

        dragleave(e) {
            // When dragging over a child, the parent will trigger a dragleave.
            if (e.target !== e.currentTarget) return;

            this.dragging = false;
        },

        drop(e) {
            e.stopPropagation();
            e.preventDefault();
            this.dragging = false;

            for (let i = 0; i < e.dataTransfer.files.length; i++) {
                this.addFile(e.dataTransfer.files[i]);
            }
        },

        addFile(file) {
            if (!this.enabled) return;

            const id = uniqid();
            const upload = this.makeUpload(id, file);

            this.uploads = [
                ...this.uploads,
                {
                    id,
                    basename: file.name,
                    extension: file.name.split('.').pop(),
                    percent: 0,
                    processing: false,
                    errorMessage: null,
                    instance: upload
                }
            ];
        },

        updateUpload(uploadId, callback) {
            this.uploads = this.uploads.map(upload => {
                if (upload.id !== uploadId) {
                    return upload;
                }

                return callback(upload)
            })
        },

        findUpload(id) {
            return this.uploads.find(u => u.id === id);
        },

        findUploadIndex(id) {
            return this.uploads.findIndex(u => u.id === id);
        },

        makeUpload(id, file) {
            const upload = new Upload({
                url: this.url,
                form: this.makeFormData(file),
                headers: {
                    Accept: 'application/json'
                }
            });

            upload.on('progress', progress => {
                this.updateUpload(id, (upload) => ({
                    ...upload,
                    percent: progress * 100
                }))
            });

            return upload;
        },

        makeFormData(file) {
            const form = new FormData();

            form.append('file', file);

            for (let key in this.extraData) {
                form.append(key, this.extraData[key]);
            }

            return form;
        },

        processUploadQueue() {
            const unprocessedUploads = this.uploads.filter(u => !u.processing)

            if (unprocessedUploads.length === 0) {
                return;
            }

            const upload = unprocessedUploads[0];
            const id = upload.id;

            this.updateUpload(id, (upload) => ({
                ...upload,
                processing: true,
            }))

            upload.instance.upload().then(response => {
                let json = null;

                try {
                    json = JSON.parse(response.data);
                } catch (error) {
                    // If it fails, it's probably because the response is HTML.
                }

                response.status === 200
                    ? this.handleUploadSuccess(id, json)
                    : this.handleUploadError(id, response.status, json);
            });
        },

        handleUploadSuccess(id, response) {
            this.$emit('upload-complete', response.data, this.uploads);

            this.uploads = this.uploads.filter((upload) => upload.id !== id);
        },

        handleUploadError(id, status, response) {
            let msg = response?.message;

            if (!msg) {
                if (status === 413) {
                    msg = __('Upload failed. The file is larger than is allowed by your server.');
                } else {
                    msg = __('Upload failed. The file might be larger than is allowed by your server.');
                }
            } else {
                if (status === 422) {
                    msg = Object.values(response.errors)[0][0]; // Get first validation message.
                }
            }

            this.updateUpload(id, (upload) => ({
                ...upload,
                errorMessage: msg,
            }))

            this.$emit('error', upload, this.uploads);
        },
    }

};
</script>
