<template>
    <div>
        <div class="flex items-center bg-gray-800 py-3 px-4 rounded-md">
            <pre class="flex-1 p-0 m-0 leading-6"><code class="bg-transparent p-0 text-gray-400 leading-none" v-text="text" /></pre>
            <button v-if="copyable" class="flex" v-tooltip="__('Copy')" @click="copy">
                <svg-icon name="entries" class="w-4 h-4 text-white" />
            </button>
        </div>
    </div>

</template>

<script>
export default {

    props: {
        text: {
            type: String,
            required: true
        },
        copyable: {
            type: Boolean,
            default: false
        }
    },

    methods: {

        async copy() {
            await navigator.clipboard.writeText(this.text);
            Statamic.$toast.success(__('Copied to clipboard'));
        }

    }

}
</script>
