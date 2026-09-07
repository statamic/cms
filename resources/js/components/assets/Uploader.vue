<script>
import { Upload } from 'upload';
import { nanoid as uniqid } from 'nanoid';
import { h } from 'vue';
import ChunkedUpload from './ChunkedUpload';
import { useUploadsStore } from '../../stores/uploads';

export default {
    emits: ['updated', 'upload-complete', 'error'],

    render() {
        const fileField = h('input', {
            class: { hidden: true },
            type: 'file',
            multiple: true,
            ref: 'nativeFileField',
        });

        const events = this.enabled
            ? {
                  onDragenter: this.dragenter,
                  onDragover: this.dragover,
                  onDragleave: this.dragleave,
                  onDrop: this.drop,
              }
            : {};

        return h(
            'div',
            {
                class: 'h-full',
                ...events,
            },
            [
                h('div', { class: ['h-full', { 'pointer-events-none': this.dragging }] }, [
                    fileField,
                    ...this.$slots.default({ dragging: this.enabled ? this.dragging : false }),
                ]),
            ],
        );
    },

    props: {
        enabled: {
            type: Boolean,
            default: () => true,
        },
        container: String,
        path: String,
        url: { type: String, default: () => cp_url('assets') },
        extraData: {
            type: Object,
            default: () => ({}),
        },
        chunkedUploads: { type: Boolean, default: false },
        chunkSize: { type: Number, default: 0 },
        maxFilesize: { type: Number, default: null },
        chunkUploadUrl: { type: String, default: null },
    },

    data() {
        return {
            dragging: false,
            uploads: [],
        };
    },

    created() {
        this.uploadsStore = useUploadsStore();
    },

    mounted() {
        this.$refs.nativeFileField.addEventListener('change', this.addNativeFileFieldSelections);
    },

    beforeUnmount() {
        this.$refs.nativeFileField.removeEventListener('change', this.addNativeFileFieldSelections);
    },

    watch: {
        uploads: {
            deep: true,
            handler(uploads) {
                this.$emit('updated', uploads);
                this.processUploadQueue();
            },
        },
    },

    computed: {
        activeUploads() {
            return this.uploads.filter((u) => u.instance.state === 'started');
        },
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
                    if (item.kind === 'file' || !item.kind) {
                        this.addFile(item.getAsFile());
                    }
                }
            }
        },

        addFilesFromDirectory(directory, path) {
            const reader = directory.createReader();
            const readEntries = () =>
                reader.readEntries((entries) => {
                    if (!entries.length) return;
                    for (let entry of entries) {
                        if (entry.isFile) {
                            entry.file((file) => {
                                if (!file.name.startsWith('.')) {
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
            if (!this.enabled) return;

            const id = uniqid();
            const tooLarge = this.maxFilesize && file.size > this.maxFilesize;

            const upload = {
                id,
                basename: file.name,
                extension: file.name.split('.').pop(),
                percent: 0,
                errorMessage: tooLarge ? __('Upload failed. The file is larger than is allowed.') : null,
                errorStatus: tooLarge ? 413 : null,
            };

            this.uploads.push({
                ...upload,
                instance: tooLarge ? { state: 'failed', form: { get: () => file } } : this.makeUpload(id, file, data),
                retry: (opts) => this.retry(id, opts),
            });

            this.uploadsStore.add(this.container, { ...upload });
        },

        findUpload(id) {
            return this.uploads.find((u) => u.id === id);
        },

        findUploadIndex(id) {
            return this.uploads.findIndex((u) => u.id === id);
        },

        makeUpload(id, file, data = {}) {
            const useChunked =
                this.chunkedUploads &&
                this.chunkSize > 0 &&
                this.chunkUploadUrl &&
                file.size >= this.chunkSize &&
                typeof file.slice === 'function';

            const upload = useChunked
                ? new ChunkedUpload({
                      url: this.chunkUploadUrl,
                      file,
                      data: this.uploadParams(file, data),
                      chunkSize: this.chunkSize,
                  })
                : new Upload({
                      url: this.url,
                      form: this.makeFormData(file, data),
                      headers: {
                          Accept: 'application/json',
                      },
                  });

            upload.on('progress', (progress) => {
                const percent = progress * 100;
                this.findUpload(id).percent = percent;
                this.uploadsStore.update(this.container, id, { percent });
            });

            return upload;
        },

        uploadParams(file, data = {}) {
            const params = {
                ...this.extraData,
                container: this.container,
                folder: this.path,
                _token: Statamic.$config.get('csrfToken'),
                ...data,
            };

            // Pass along the relative path of files uploaded as a directory
            if (file.relativePath) {
                params.relativePath = file.relativePath;
            }

            return params;
        },

        makeFormData(file, data = {}) {
            const form = new FormData();

            form.append('file', file);

            const params = this.uploadParams(file, data);

            for (let key in params) {
                form.append(key, params[key]);
            }

            return form;
        },

        processUploadQueue() {
            // If we're already uploading, don't start another
            if (this.activeUploads.length) return;

            // Make sure we're not grabbing a running or failed upload
            const upload = this.uploads.find((u) => u.instance.state === 'new' && !u.errorMessage);
            if (!upload) return;

            const id = upload.id;

            upload.instance.upload().then((response) => {
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
            this.uploadsStore.remove(this.container, id);

            this.handleToasts(response._toasts ?? []);
        },

        handleUploadError(id, status, response) {
            const upload = this.findUpload(id);
            let msg = response?.message;
            if (!msg) {
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

            this.handleToasts(response?._toasts ?? []);

            upload.errorMessage = msg;
            upload.errorStatus = status;
            this.uploadsStore.update(this.container, id, { errorMessage: msg, errorStatus: status });
            this.$emit('error', upload, this.uploads);
            this.processUploadQueue();
        },

        handleToasts(toasts) {
            toasts.forEach((toast) => Statamic.$toast[toast.type](toast.message, { duration: toast.duration }));
        },

        retry(id, args) {
            let file = this.findUpload(id).instance.form.get('file');
            this.addFile(file, args);
            this.uploads.splice(this.findUploadIndex(id), 1);
            this.uploadsStore.remove(this.container, id);
        },
    },
};
</script>
