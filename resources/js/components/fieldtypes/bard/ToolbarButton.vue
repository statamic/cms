<template>

    <button
        :class="{ active }"
        v-html="button.html"
        v-tooltip="button.text"
        @click="editor.commands[button.command](button.args)"
    />

</template>

<script>
export default {

    props: {
        button: Object,
        active: Boolean,
        config: Object,
        editor: {}
    }

}
</script>
