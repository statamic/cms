<template>
    <div class="wizard-steps">
        <div class="step" v-for="(step, index) in steps">
            <div class="ball">{{ ++index }}</div>
            <div class="label">{{ step }}</div>
        </div>
    </div>

</template>

<script>
export default {
    props: {
        steps: {
            type: Array
        }
    },

    methods: {

    }
}
</script>
