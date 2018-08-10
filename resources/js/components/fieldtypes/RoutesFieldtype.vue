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
                add_row: translate('cp.add_locale'),
                fields: [
                    { name: 'locale', type: 'text', display: translate('cp.locale'), width: '20' },
                    { name: 'route', type: 'text', display: translate('cp.route') },
                ]
            }
        }
    }

};
</script>
