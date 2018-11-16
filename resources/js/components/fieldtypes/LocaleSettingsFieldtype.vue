<template>
    <div class="locale-settings-fieldtype-wrapper">
        <grid-fieldtype :data="data" :config="gridConfig"></grid-fieldtype>
        <div class="alert alert-danger mt-16" v-if="shouldShowIndexPhpWarning">
            <small v-html="__('cp.settings_locales_index_php_warning', { locale: firstLocale })"></small>
        </div>
    </div>
</template>

<script>
export default {

    props: ['data', 'config', 'name', 'indexPhpWarning'],

    computed: {

        shouldShowIndexPhpWarning() {
            return this.indexPhpWarning && this.firstLocale !== 'en';
        },

        firstLocale() {
            return this.data.length ? this.data[0].locale : 'en';
        }

    },

    data: function() {
        return {
            gridConfig: {
                add_row: __('Locale'),
                fields: [
                    { name: 'locale', type: 'text', display: __('Shorthand'), instructions: __('cp.shorthand_instructions'), width: '20%' },
                    { name: 'full', type: 'text', display: __('Full Locale'), instructions: __('cp.full_locale_instructions'), width: '20%' },
                    { name: 'name', type: 'text', display: __('Name'), instructions: __('cp.locale_name_instructions'), width: '20%' },
                    { name: 'url', type: 'text', display: __('URL'), instructions: __('cp.locale_url_instructions') }
                ]
            }
        }
    }

};
</script>
