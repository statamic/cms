<template>

    <div class="form-group" :class="{ 'locale-status-field major': multipleLocales }">

        <template v-if="singleLocale && allowStatuses">
            <label class="block">{{ translate('cp.published') }}</label>
            <toggle-fieldtype :data.sync="status"></toggle-fieldtype>
        </template>

        <template v-if="multipleLocales">
            <label class="block">{{ translate_choice('cp.locales', 2) }}</label>
            <div class="locale-item" v-for="locale in locales">
                <template v-if="locale.is_active">
                    <span v-if="!allowStatuses" class="icon-status icon-status-live"></span>
                    {{ locale.label }}
                    <span v-if="locale.is_active" class="check">✔</span>
                    <toggle-fieldtype v-if="allowStatuses" :data.sync="status"></toggle-fieldtype>
                </template>
                <template v-else>
                    <span class="icon-status {{ statusClass(locale) }}"></span>
                    <a :href="locale.url">{{ locale.label }}</a>
                </template>
            </div>
        </template>

    </div>

</template>


<style lang="scss">
    .locale-status-field {
        .locale-item {
            font-size: 14px;
            padding: 5px 15px 5px 0;
            border-top: 1px solid #eee;

            .icon-status {
                float: right;
                margin-top: 7px;
            }
            .check {
                margin-left: 5px;
                color: #aaa;
            }
        }
        .toggle-fieldtype-wrapper {
            float: right;
            margin-right: -5px;
        }
        .toggle-container {
            margin: 0;
            height: 17px;
            width: 34px;
        }
        .toggle-knob {
            width: 16px;
            height: 16px;
            top: 0;
        }
        .toggle-slider {
            width: 16px;
            height: 17px;
            top: 0;
        }
        .toggle-container.on .toggle-slider {
            width: 34px;
        }
    }
</style>


<script>

export default {

    props: ['locale', 'locales', 'status', 'allowStatuses'],

    computed: {

        singleLocale() {
            return this.locales.length === 1;
        },

        multipleLocales() {
            return ! this.singleLocale;
        }

    },

    methods: {

        statusClass(locale) {
            if (!this.allowStatuses) return 'icon-status-live';

            return locale.is_published ? 'icon-status-live' : 'icon-status-hidden';

            if (locale.has_content && locale.is_published) {
                return 'icon-status-live';
            } else if (locale.has_content && !locale.is_published) {
                return 'icon-status-off';
            }
            return 'icon-status-hidden';
        }

    }

}

</script>
