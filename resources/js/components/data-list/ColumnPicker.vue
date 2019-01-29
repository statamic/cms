<template>
    <dropdown-list>
        <button class="btn btn-icon-only antialiased ml-2 dropdown-toggle" slot="trigger">
            <svg-icon name="picker" class="h-4 w-4 mr-1 text-current"></svg-icon>
            <span>{{ __('Columns') }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-checkboxes">
            <li>
                <h6>{{ __('Customize Columns') }}</h6>
            </li>
            <li class="divider"></li>
            <li v-for="column in sharedState.columns">
                <input type="checkbox"
                    v-model="sharedState.visibleColumns"
                    :value="column"
                    @change="$emit('change', sharedState.visibleColumns)">
                {{ column }}
            </li>
        </ul>
    </dropdown-list>
</template>
<script>
export default {
    inject: ['sharedState']
}
</script>
