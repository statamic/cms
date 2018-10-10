<template>

    <div class="blueprint-section-field">
        <div class="blueprint-drag-handle w-4 border-r"></div>
        <div class="px-2 py-1">
            <span class="font-medium mr-1">{{ field.handle }}</span>
            <span class="font-mono text-2xs text-grey-light">{{ field.handle }}</span>
        </div>
    </div>

</template>

<script>
export default {

    props: {
        field: {
            type: Object,
            required: true
        }
    }

}
</script>
