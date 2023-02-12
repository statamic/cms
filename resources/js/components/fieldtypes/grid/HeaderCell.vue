<template>

    <th v-show="field.type !== 'hidden'">
        <div class="flex items-center justify-between">
            <div>{{ field.display || field.handle }}</div>
            <svg-icon name="micro-circle-help" class="text-gray-600 hover:text-gray-700 h-3 w-3 text-xs" v-if="field.instructions" v-tooltip="$options.filters.markdown(field.instructions)" />
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
