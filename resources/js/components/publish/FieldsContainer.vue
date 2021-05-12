<template>

    <element-container @resized="width = $event.width">
        <div class="publish-fields" :class="{ 'publish-fields-narrow': width <= 600 }">
            <slot />
        </div>
    </element-container>

</template>

<script>
export default {
    data() {
        return {
            width: null
        }
    }
}
</script>
