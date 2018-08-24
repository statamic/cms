<template>
    <ul class="list-reset flex items-center">
        <li v-for="column in sharedState.columns" class="ml-1">
            <label>
                <input type="checkbox" v-model="sharedState.visibleColumns" :value="column" @change="$emit('change', sharedState.visibleColumns)"> {{ column }}
            </label>
        </li>
    </ul>
</template>
<script>
export default {
    inject: ['sharedState']
}
</script>
