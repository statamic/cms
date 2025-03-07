<script>
import { Upload } from 'upload';
import uniqid from 'uniqid';

export default {

    render(h) {
        const fileField = h('input', {
            class: { hidden: true },
            attrs: { type: 'file', multiple: true },
            ref: 'nativeFileField'
        });

        return h('div', { on: {
            'dragenter': this.dragenter,
            'dragover': this.dragover,
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
        url: { type: String, default: () => cp_url('assets') },
        extraData: {
            type: Object,
            default: () => ({})
        }
    },


    data() {
        return {
            dragging: false,
            uploads: []
        }
    },


    mounted() {
        this.$refs.nativeFileField.addEventListener('change', this.addNativeFileFieldSelections);
    },


    beforeDestroy() {
        this.$refs.nativeFileField.removeEventListener('change', this.addNativeFileFieldSelections);
    },


    watch: {

        uploads(uploads) {
            this.$emit('updated', uploads);
            this.processUploadQueue();
        }

    },


    computed: {

        activeUploads() {
            return this.uploads.filter(u => u.instance.state === 'started');
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

            const { files, items } = e.dataTransfer;

            // Handle DataTransferItems if browser supports dropping of folders
            if (items && items.length && items[0].webkitGetAsEntry) {
                this.addFilesFromDataTransferItems(items);
            } else {
                this.addFilesFromFileList(files);
            }
        },

        addFilesFromFileList(files) {
            for (let i = 0; i < files.length; i++) {
                this.addFile(files[i]);
            }
        },

        addFilesFromDataTransferItems(items) {
            for (let i = 0; i < items.length; i++) {
                let item = items[i];
                if (item.webkitGetAsEntry) {
                    const entry = item.webkitGetAsEntry();
                    if (entry?.isFile) {
                        this.addFile(item.getAsFile());
                    } else if (entry?.isDirectory) {
                        this.addFilesFromDirectory(entry, entry.name);
                    }
                } else if (item.getAsFile) {
                    if (item.kind === "file" || ! item.kind) {
                        this.addFile(item.getAsFile());
                    }
                }
            }
        },

        addFilesFromDirectory(directory, path) {
            const reader = directory.createReader();
            const readEntries = () => reader.readEntries((entries) => {
                if (!entries.length) return;
                for (let entry of entries) {
                    if (entry.isFile) {
                        entry.file((file) => {
                            if (! file.name.startsWith('.')) {
                                file.relativePath = path;
                                this.addFile(file);
                            }
                        });
                    } else if (entry.isDirectory) {
                        this.addFilesFromDirectory(entry, `${path}/${entry.name}`);
                    }
                }
                // Handle directories with more than 100 files in Chrome
                readEntries();
            }, console.error);
            return readEntries();
        },

        addFile(file, data = {}) {
            if (! this.enabled) return;

            const id = uniqid();
            const upload = this.makeUpload(id, file, data);

            this.uploads.push({
                id,
                basename: file.name,
                extension: file.name.split('.').pop(),
                percent: 0,
                errorMessage: null,
                errorStatus: null,
                instance: upload,
                retry: (opts) => this.retry(id, opts)
            });
        },

        findUpload(id) {
            return this.uploads.find(u => u.id === id);
        },

        findUploadIndex(id) {
            return this.uploads.findIndex(u => u.id === id);
        },

        makeUpload(id, file, data = {}) {
            const upload = new Upload({
                url: this.url,
                form: this.makeFormData(file, data),
                headers: {
                    Accept: 'application/json'
                }
            });

            upload.on('progress', progress => {
                this.findUpload(id).percent = progress * 100;
            });

            return upload;
        },

        makeFormData(file, data = {}) {
            const form = new FormData();

            form.append('file', file);

            // Pass along the relative path of files uploaded as a directory
            if (file.relativePath) {
                form.append('relativePath', file.relativePath);
            }

            let parameters = {
                ...this.extraData,
                container: this.container,
                folder: this.path,
                _token: Statamic.$config.get('csrfToken')
            }

            for (let key in parameters) {
                form.append(key, parameters[key]);
            }

            for (let key in data) {
                form.append(key, data[key]);
            }

            return form;
        },

        processUploadQueue() {
            // If we're already uploading, don't start another
            if (this.activeUploads.length) return;

            // Make sure we're not grabbing a running or failed upload
            const upload = this.uploads.find(u => u.instance.state === 'new' && !u.errorMessage);
            if (!upload) return;

            const id = upload.id;

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

                this.processUploadQueue();
            });
        },

        handleUploadSuccess(id, response) {
            this.$emit('upload-complete', response.data, this.uploads);
            this.uploads.splice(this.findUploadIndex(id), 1);
        },

        handleUploadError(id, status, response) {
            const upload = this.findUpload(id);
            let msg = response?.message;
            if (! msg) {
                if (status === 413) {
                    msg = __('Upload failed. The file is larger than is allowed by your server.');
                } else {
                    msg = __('Upload failed. The file might be larger than is allowed by your server.');
                }
            } else {
                if ([422, 409].includes(status)) {
                    msg = Object.values(response.errors)[0][0]; // Get first validation message.
                }
            }
            upload.errorMessage = msg;
            upload.errorStatus = status;
            this.$emit('error', upload, this.uploads);
            this.processUploadQueue();
        },

        retry(id, args) {
            let file = this.findUpload(id).instance.form.get('file');
            this.addFile(file, args);
            this.uploads.splice(this.findUploadIndex(id), 1);
        }
    }

}
</script>
