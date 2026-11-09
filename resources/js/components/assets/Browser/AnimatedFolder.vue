<template>
    <svg viewBox="0 0 80 66" fill="none" xmlns="http://www.w3.org/2000/svg" class="overflow-visible">
        <path :d="folderPath" fill="currentColor" />

        <template v-if="drop">
            <rect x="0" y="15.4" width="80" height="50.6" rx="7.5" class="fill-black/25" />

            <g :key="drop.fileKey" class="origin-center [transform-box:fill-box] motion-safe:animate-folder-file">
                <g v-for="sheet in sheets" :key="sheet.index" :transform="sheet.transform">
                    <rect x="25" y="20" width="30" height="38" rx="2.5" class="fill-white stroke-black/10" />

                    <template v-if="sheet.thumbnail">
                        <image
                            :href="sheet.thumbnail"
                            x="28"
                            y="23"
                            width="24"
                            height="24"
                            preserveAspectRatio="xMidYMid slice"
                        />
                        <rect x="28" y="50" width="16" height="2" rx="1" class="fill-gray-300" />
                    </template>

                    <template v-else>
                        <rect x="29" y="25" width="22" height="2" rx="1" class="fill-gray-300" />
                        <rect x="29" y="30" width="22" height="2" rx="1" class="fill-gray-300" />
                        <rect x="29" y="35" width="22" height="2" rx="1" class="fill-gray-300" />
                        <rect x="29" y="40" width="14" height="2" rx="1" class="fill-gray-300" />
                    </template>
                </g>
            </g>
        </template>

        <rect
            :key="drop?.id ?? 'flap'"
            x="0"
            y="15.4"
            width="80"
            height="50.6"
            rx="7.5"
            fill="currentColor"
            class="origin-bottom [transform-box:fill-box]"
            :class="{
                'motion-safe:animate-folder-open': drop && !drop.closing,
                'motion-safe:animate-folder-close': drop?.closing,
            }"
        />
    </svg>
</template>

<script>
export default {
    props: {
        /**
         * The drop to animate into the folder, as `{ id, fileKey, files: [{ thumbnail }], closing }`.
         * The folder opens and stays open until `closing` is set. Up to three files are shown.
         * Changing the `id` replays the whole animation, and changing `fileKey` drops the files in again.
         */
        drop: {
            type: Object,
            default: null,
        },
    },

    computed: {
        sheets() {
            // Rotations pivot around the sheet's center (40, 39) so the bundle fans out from the middle.
            const fan = ['', 'translate(-6 2) rotate(-8 40 39)', 'translate(6 1) rotate(7 40 39)'];
            const files = this.drop?.files?.length ? this.drop.files.slice(0, 3) : [{}];

            return files
                .map((file, index) => ({ index, thumbnail: file.thumbnail, transform: fan[index] }))
                .reverse();
        },
    },

    data() {
        return {
            folderPath:
                'M73.6842 8.8H33.6842C33.1152 8.79854 32.5701 8.56117 32.1684 8.14L26.5263 2.2C25.9489 1.53249 25.2486 0.993597 24.4672 0.615578C23.6859 0.237558 22.8396 0.0281878 21.9789 0H7.53684C5.53795 0 3.62092 0.82979 2.20749 2.30683C0.794058 3.78386 0 5.78715 0 7.876V58.124C0 60.2128 0.794058 62.2161 2.20749 63.6932C3.62092 65.1702 5.53795 66 7.53684 66H72.4632C74.4621 66 76.3791 65.1702 77.7925 63.6932C79.2059 62.2161 80 60.2128 80 58.124V15.4C80 13.6496 79.3346 11.9708 78.1501 10.7331C76.9657 9.49535 75.3593 8.8 73.6842 8.8Z',
        };
    },
};
</script>
