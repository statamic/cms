import { defineStore } from 'pinia';

/**
 * Tracks in-progress Control Panel uploads, keyed by asset container handle.
 *
 * Upload state normally lives inside the per-page Uploader component, so it is
 * lost when navigating away (even though the upload keeps running). The asset
 * Uploader writes its lifecycle here so that consumers can later read this store
 * to restore progress for uploads still running in the background.
 */
export const useUploadsStore = defineStore('uploads', {
    state: () => ({
        byContainer: {},
    }),

    getters: {
        forContainer: (state) => (container) => state.byContainer[container] ?? [],
    },

    actions: {
        add(container, upload) {
            (this.byContainer[container] ??= []).push(upload);
        },

        update(container, id, values) {
            const upload = (this.byContainer[container] ?? []).find((u) => u.id === id);

            if (upload) Object.assign(upload, values);
        },

        remove(container, id) {
            const remaining = (this.byContainer[container] ?? []).filter((u) => u.id !== id);

            if (remaining.length) {
                this.byContainer[container] = remaining;
            } else {
                delete this.byContainer[container];
            }
        },
    },
});
