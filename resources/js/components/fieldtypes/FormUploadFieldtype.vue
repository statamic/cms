<template>
    <div class="@container relative">
        <div
            v-if="canUpload"
            @dragenter="dragenter"
            @dragover="dragover"
            @dragleave="dragleave"
            @drop="drop"
        >
            <!-- While dragging, children can't be hit-tested, so every drag event during this
                 drag session lands on this wrapper — never bubbling up from a child with a
                 mismatched target/currentTarget, which would otherwise leave `dragging` stuck. -->
            <div :class="{ 'pointer-events-none': dragging || isReadOnly, 'opacity-60': isReadOnly }">
                <div
                    v-show="dragging"
                    class="absolute inset-0 z-(--z-index-above) flex gap-2 items-center justify-center bg-white/80 border border-gray-400 border-dashed rounded-lg"
                >
                    <ui-icon name="upload-cloud" class="size-5 text-gray-500" />
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Drop to Upload') }}</div>
                </div>

                <div class="border border-gray-400 dark:border-gray-700 border-dashed rounded-xl p-4 flex flex-col @2xs:flex-row items-center gap-4" :class="{ 'rounded-b-none': files.length }">
                    <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center flex-1 justify-center">
                        <ui-icon name="upload-cloud" class="size-5 text-gray-500 me-2" />
                        <span v-text="`${__('Drag & drop here or')}&nbsp;`" />
                        <button type="button" class="underline underline-offset-2 cursor-pointer hover:text-gray-925 dark:hover:text-gray-200" :disabled="isReadOnly" @click.prevent="browse">
                            {{ __('choose a file') }}
                        </button>
                        <span>.</span>
                    </div>
                </div>

                <input
                    ref="input"
                    :id="id"
                    type="file"
                    :name="name"
                    :multiple="config.max_files !== 1"
                    class="hidden"
                    :disabled="isReadOnly"
                    @change="filesSelected"
                >
            </div>
        </div>

        <div v-if="files.length" class="relative overflow-hidden rounded-xl border border-gray-300 dark:border-gray-700" :class="{ 'border-t-0! rounded-t-none': canUpload }">
            <table class="w-full">
                <tbody>
                    <tr
                        v-for="(file, i) in files"
                        :key="i"
                        class="asset-row bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-900"
                    >
                        <td class="flex gap-2 sm:gap-3 h-full items-center p-3">
                            <div class="flex size-7 items-center justify-center whitespace-nowrap">
                                <file-icon :extension="getExtension(file.filename)" class="size-7" />
                            </div>
                            <div class="flex w-full flex-1 items-center truncate text-sm text-gray-600 dark:text-gray-400 text-start">
                                <a v-if="file.download_url" :href="file.download_url" class="underline underline-offset-2">{{ file.filename }}</a>
                                <span v-else v-text="file.filename" />
                            </div>
                        </td>
                        <td v-if="file.size" class="p-3 align-middle text-end text-sm text-gray-500 whitespace-nowrap">
                            {{ file.size }}
                        </td>
                        <td v-if="!isLocked" class="p-3 align-middle text-end">
                            <ui-button
                                :disabled="isReadOnly"
                                @click="remove(i)"
                                icon="x"
                                round
                                size="xs"
                                variant="ghost"
                                :aria-label="__('Remove')"
                                :title="__('Remove')"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else-if="!canUpload" class="text-sm text-gray-500" v-text="__('No files')" />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

    data() {
        return {
            dragging: false,
        };
    },

    computed: {
        selectedFiles() {
            if (!this.value) return [];

            return Array.isArray(this.value) ? this.value : [this.value];
        },

        hasPendingSelection() {
            return this.selectedFiles.some(file => file instanceof File);
        },

        // `value` is the source of truth for what's currently attached — a real File object
        // for a fresh, unsaved selection, or a plain string once it's been processed server-side.
        // `meta.files` is only used to enrich a still-present entry with size/download data; it's
        // never used to decide whether an entry exists, otherwise a removed file's stale meta
        // would keep its row on screen after the value's been cleared.
        files() {
            return this.selectedFiles.map((file, i) => {
                if (file instanceof File) {
                    return { filename: file.name };
                }

                return this.meta.files?.[i] ?? { filename: typeof file === 'string' ? file.split('/').pop() : String(file) };
            });
        },

        // True once the field is genuinely locked from any interaction — permanently read-only
        // (viewing a finalized submission), or transiently read-only (mid-submit) with nothing
        // pending to keep showing.
        isLocked() {
            return this.isReadOnly && !this.hasPendingSelection;
        },

        // The dropzone only makes sense while more files can still be added: not locked, and
        // not already at a single-file field's limit (removing the existing file makes room again).
        canUpload() {
            return !this.isLocked && (this.config.max_files !== 1 || this.selectedFiles.length === 0);
        },
    },

    methods: {
        browse() {
            this.$refs.input.click();
        },

        filesSelected(event) {
            this.addFiles(event.target.files);
            event.target.value = null;
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

            this.addFiles(e.dataTransfer.files);
        },

        addFiles(fileList) {
            const files = Array.from(fileList);

            if (this.config.max_files === 1) {
                this.update(files[0] ?? this.value);
                return;
            }

            const combined = [...this.selectedFiles, ...files];

            this.update(this.config.max_files ? combined.slice(0, this.config.max_files) : combined);
        },

        remove(index) {
            if (this.config.max_files === 1) {
                this.update(null);
                return;
            }

            this.update([...this.selectedFiles.slice(0, index), ...this.selectedFiles.slice(index + 1)]);
        },

        getExtension(filename) {
            return filename?.split('.').pop() ?? '';
        },
    },
};
</script>
