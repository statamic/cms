<template>
    <div>
        <div
            class="flex items-center bg-gray-800 py-3 px-4 rounded-md"
            ref="copyhighlight"
        >
            <pre
                class="flex-1 p-0 m-0 leading-6"
            ><code class="bg-transparent p-0 text-gray-400 leading-none" v-text="text" /></pre>
            <button
                v-if="copyable"
                class="flex"
                v-tooltip="__('Copy')"
                @click="copy"
            >
                <svg-icon name="light/entries" class="w-4 h-4 text-white" />
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        text: {
            type: String,
            required: true,
        },
        copyable: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        async copy() {
            // Workaround for Google Chrome
            // Chrome allows navigator.clipboard only in secure context - https://web.dev/async-clipboard/
            const permissionStatus = await navigator.permissions.query({
                name: "clipboard-write",
                allowWithoutGesture: false,
            });

            switch (permissionStatus.state) {
                case "granted":
                    this.nativeCopy();
                    break;
                case "denied":
                case "prompt":
                    this.workaroundCopy();
                    break;
            }
        },
        nativeCopy() {
            navigator.clipboard.writeText(
                this.text,
                () => {
                    Statamic.$toast.success(__("Copied to clipboard"));
                },
                () => {
                    Statamic.$toast.error(__("Copied to clipboard failed"));
                }
            );
        },
        workaroundCopy() {
            if (this.$refs.copyhighlight) {
                const textarea = document.createElement("textarea");
                textarea.classList.add("sr-only");
                textarea.value = this.text;

                this.$refs.copyhighlight.appendChild(textarea);
                textarea.select();
                const copied = document.execCommand("copy");

                if (copied) {
                    Statamic.$toast.success(__("Copied to clipboard"));
                } else {
                    Statamic.$toast.error(__("Copied to clipboard failed"));
                }

                textarea.remove();
            } else {
                Statamic.$toast.error(__("Copied to clipboard failed"));
            }
        },
    },
};
</script>
