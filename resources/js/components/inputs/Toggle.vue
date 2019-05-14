<template>
    <div class="toggle-container" :class="{ 'on': value }" @click="toggle" role="checkbox">
        <div class="toggle-slider">
            <div class="toggle-knob" tabindex="0" @keyup.prevent.space.enter="toggle" ref="knob" />
        </div>
    </div>
</template>

<script>

export default {
    props: ["value"],

    methods: {
        toggle() {
            this.$emit("input", !this.value)
        }
    }

}
</script>
