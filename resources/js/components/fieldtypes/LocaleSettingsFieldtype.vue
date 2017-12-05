<template>
    <div class="locale-settings-fieldtype-wrapper">
        <grid-fieldtype :data="data" :config="gridConfig"></grid-fieldtype>
    </div>
</template>

<script>
module.exports = {

    props: ['data', 'config', 'name'],

    data: function() {
        return {
            gridConfig: {
                add_row: 'Add Locale',
                fields: [
                    { name: 'locale', type: 'text', display: 'Shorthand', instructions: '2 character code.<br> `en`, `de`, etc.', width: '20%' },
                    { name: 'full', type: 'text', display: 'Full Locale', instructions: 'Used for PHP date localization.<br>`en_US`, `de_DE`, etc.', width: '20%' },
                    { name: 'name', type: 'text', display: 'Name', instructions: 'Used for display.<br> `English`, `German`, etc', width: '20%' },
                    { name: 'url', type: 'text', display: 'URL', instructions: '`http://example.com/de/`, etc.' }
                ]
            }
        }
    }

};
</script>
