<template>
    <div class="routes-fieldtype-wrapper">
        <grid-fieldtype :data="data" :config="gridConfig"></grid-fieldtype>
        <input type="hidden" :name="name" :value="data | json" />
    </div>
</template>

<script>
export default {

    props: ['data', 'config', 'name'],

    data: function() {
        return {
            gridConfig: {
                add_row: __('Add Locale'),
                fields: [
                    { name: 'locale', type: 'text', display: __('Locale'), width: '20' },
                    { name: 'route', type: 'text', display: __('Route') },
                ]
            }
        }
    }

};
</script>
