<template>
    <div>
        <div class="flex items-center rounded-md bg-gray-800 px-4 py-3">
            <pre
                class="m-0 flex-1 p-0 leading-6"
            ><code class="bg-transparent p-0 text-gray-400 leading-none" v-text="text" /></pre>
            <button v-if="canCopy" class="flex" v-tooltip="__('Copy')" @click="copy">
                <svg-icon name="light/entries" class="h-4 w-4 text-white" />
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

    computed: {
        canCopy() {
            return this.copyable && window.isSecureContext;
        },
    },

    methods: {
        async copy() {
            await navigator.clipboard.writeText(this.text);
            Statamic.$toast.success(__('Copied to clipboard'));
        },
    },
};
</script>
