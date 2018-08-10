<template>
    <div class="redactor-settings-wrapper">
        <select-fieldtype :name="name" :data.sync="data" :config="selectConfig"></select-fieldtype>
    </div>
</template>

<script>
export default {

    props: ['data', 'name', 'config'],

    computed: {
        selectConfig: function () {
            var options = [{ value: null, text: '' }];

            _.each(Statamic.redactorSettings, function (config, key) {
                options.push({
                    value: key,
                    text: key
                });
            });

            return { options };
        }
    }
};
</script>
