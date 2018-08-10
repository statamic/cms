<template>

    <div class="mr-2">

        <template v-if="singleLocale && allowStatuses">
            <button class="btn btn-default status-field" @click.prevent="status = !status">
                <span class="mr-8 icon-status {{ status ? 'icon-status-live' : 'icon-status-hidden' }}"></span>
                {{ status ? translate('cp.published') : translate('cp.draft') }}
            </button>
        </template>

        <div class="locale-status-field" :class="{ open: isShowingLocales }" v-if="multipleLocales">
            <button class="btn btn-default dropdown-toggle" @click.prevent="isShowingLocales = !isShowingLocales">
                <span class="mr-8 icon-status {{ status ? 'icon-status-live' : 'icon-status-hidden' }}"></span>
                {{ currentLocaleLabel }}
            </button>
            <div class="dropdown-menu">
                <div class="locale-item" v-for="locale in locales">
                    <template v-if="locale.is_active">
                        <span v-if="!allowStatuses" class="icon-status icon-status-live"></span>
                        {{ locale.label }}
                        <toggle-fieldtype v-if="allowStatuses" :data.sync="status"></toggle-fieldtype>
                    </template>
                    <template v-else>
                        <span class="icon-status {{ statusClass(locale) }}"></span>
                        <a :href="locale.url">{{ locale.label }}</a>
                    </template>
                </div>
            </div>
        </div>

    </div>

</template>

<style lang="scss">

    .locale-status-field {
        position: relative;

        .dropdown-menu {
            padding: 15px;
        }

        .locale-item {
            font-size: 14px;
            padding: 5px 15px 5px 0;
            border-top: 1px solid #eee;

            &:first-child {
                border-top: 0;
            }

            .icon-status {
                float: right;
                margin-top: 7px;
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

    .status-field .icon-status {
        position: relative;
        top: -1px;
    }

    @media (max-width: 768px) {
        .locale-status-field .dropdown-menu {
            left: 0;
            right: auto;
        }
    }
</style>


<script>

export default {

    props: ['locale', 'locales', 'status', 'allowStatuses'],

    data() {
        return {
            isShowingLocales: false,
        }
    },

    computed: {

        singleLocale() {
            return this.locales.length === 1;
        },

        multipleLocales() {
            return ! this.singleLocale;
        },

        currentLocaleLabel() {
            return _.find(this.locales, { name: this.locale }).label;
        }

    },

    methods: {

        statusClass(locale) {
            if (!this.allowStatuses) return 'icon-status-live';

            return locale.is_published ? 'icon-status-live' : 'icon-status-hidden';
        }

    }

}

</script>
