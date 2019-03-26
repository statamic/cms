<template>

    <div>
        <button class="btn btn-flat" @click="previewing = true">
            Preview
        </button>

        <stack name="revision-preview" v-if="previewing" @closed="previewing = false">
            <div class="bg-white h-full p-3 overflow-auto">
                <pre
                    class="whitespace-pre-wrap text-xs font-mono"
                    v-text="JSON.stringify(revision.attributes, null, 2)" />
            </div>
        </stack>
    </div>

</template>

<script>
export default {

    props: {
        revision: Object
    },

    data() {
        return {
            previewing: false
        }
    }

}
</script>
