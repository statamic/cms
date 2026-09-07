import axios from 'axios';
import { nanoid } from 'nanoid';

/**
 * Uploads a single file in sequential chunks to the chunk endpoint, then resolves
 * with the final assembly response. Duck-typed to the `upload` package so the
 * surrounding Uploader component treats it identically to a single-request upload.
 */
export default class ChunkedUpload {
    constructor({ url, file, data = {}, chunkSize, http = axios, wait, maxRetries = 3, baseDelay = 500 }) {
        this.url = url;
        this.file = file;
        this.data = data;
        this.chunkSize = chunkSize;
        this.http = http;
        this.wait = wait ?? ((ms) => new Promise((resolve) => setTimeout(resolve, ms)));
        this.maxRetries = maxRetries;
        this.baseDelay = baseDelay;
        this.uploadId = nanoid();
        this.state = 'new';
        this.uploadedBytes = 0;
        this.progressHandlers = [];
        this.form = { get: (key) => (key === 'file' ? this.file : undefined) };
    }

    on(event, handler) {
        if (event === 'progress') this.progressHandlers.push(handler);
    }

    emitProgress(value) {
        this.progressHandlers.forEach((handler) => handler(value));
    }

    async upload() {
        this.state = 'started';

        const totalChunks = Math.ceil(this.file.size / this.chunkSize);

        for (let index = 0; index < totalChunks; index++) {
            const start = index * this.chunkSize;
            const end = Math.min(start + this.chunkSize, this.file.size);
            const chunk = this.file.slice(start, end);

            let response;

            try {
                response = await this.sendChunk(chunk, index, totalChunks);
            } catch (error) {
                this.state = 'failed';

                return this.toResponse(error.response);
            }

            this.uploadedBytes = end;

            // The final chunk's response carries the assembled asset, or a validation error.
            if (index === totalChunks - 1) {
                this.state = 'finished';
                this.emitProgress(1);

                return this.toResponse(response);
            }

            this.emitProgress(this.uploadedBytes / this.file.size);
        }
    }

    async sendChunk(chunk, index, totalChunks) {
        for (let attempt = 0; ; attempt++) {
            try {
                return await this.http.post(this.url, this.formData(chunk, index, totalChunks), {
                    headers: { Accept: 'application/json' },
                    onUploadProgress: (e) => {
                        const loaded = Math.min(e.loaded, chunk.size);
                        this.emitProgress((this.uploadedBytes + loaded) / this.file.size);
                    },
                });
            } catch (error) {
                if (attempt >= this.maxRetries || !this.isRetryable(error, index, totalChunks)) {
                    throw error;
                }

                await this.wait(this.baseDelay * 2 ** attempt);
            }
        }
    }

    isRetryable(error, index, totalChunks) {
        const status = error.response?.status;

        // A network error has no response.
        if (!status) return true;

        // Validation and conflict responses are definitive.
        if ([422, 409].includes(status)) return false;

        // A proxy rejecting the assembly request is not recoverable by retrying.
        if (status === 413 && index === totalChunks - 1) return false;

        return [408, 413, 429].includes(status) || status >= 500;
    }

    formData(chunk, index, totalChunks) {
        const form = new FormData();

        form.append('chunk', chunk, this.file.name);
        form.append('uploadId', this.uploadId);
        form.append('chunkIndex', index);
        form.append('totalChunks', totalChunks);

        for (const key in this.data) {
            form.append(key, this.data[key]);
        }

        return form;
    }

    toResponse(response) {
        return {
            status: response?.status ?? 0,
            data: JSON.stringify(response?.data ?? {}),
        };
    }
}
