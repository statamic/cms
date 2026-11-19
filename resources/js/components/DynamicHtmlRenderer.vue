<template>
    <component :is="dynamicComponent" />
</template>

<script>
import { defineComponent } from 'vue'

export default {
    props: {
        html: { type: String, required: true }
    },

    computed: {
        dynamicComponent() {
            return defineComponent({ template: this.html })
        }
    }
}
</script>
