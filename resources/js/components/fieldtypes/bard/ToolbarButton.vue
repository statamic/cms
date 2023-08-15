<template>

    <button
        class="bard-toolbar-button"
        :class="{ active }"
        :aria-label="button.text"
        v-tooltip="button.text"
        @click="button.command(editor, button.args)"
    >
        <svg-icon :name="button.svg" v-if="button.svg"></svg-icon>
        <div class="flex items-center" v-html="button.html" v-if="button.html"></div>
    </button>

</template>

<script>
export default {

    props: {
        button: Object,
        active: Boolean,
        config: Object,
        bard: {},
        editor: {}
    }

}
</script>
