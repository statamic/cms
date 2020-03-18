<template>

    <th v-show="field.type !== 'hidden'">
        <div class="flex items-center justify-between">
            <div>{{ field.display || field.handle }}</div>
            <div
                v-if="field.instructions"
                class="icon icon-help-with-circle opacity-50 text-xs"
                v-tooltip="$options.filters.markdown(field.instructions)"
            />
        </div>
    </th>

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
