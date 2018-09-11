<template>
    <div>
        <svg
            :width="`${size}px`"
            :height="`${size}px`"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-dual-ring" style="background: none;"><circle cx="50" cy="50"  fill="none" stroke-linecap="round" r="40" stroke-width="10" stroke="#737f8c" stroke-dasharray="62.83185307179586 62.83185307179586" transform="rotate(107.924 50 50)"><animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 50 50;360 50 50" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform></circle></svg>

        <div class="mt-1">{{ text }}</div>
    </div>
</template>

<script>
export default {

    props: {
        text: {
            type: String,
            default: 'Loading'
        },
        size: {
            type: Number,
            default: 24
        }
    }

}
</script>



