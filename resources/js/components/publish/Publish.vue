<template>

    <div class="content-type-{{ contentType }}">
        <div class="publish-form" id="publish-form">

            <div class="publish-errors alert alert-danger" v-if="hasErrors">
                <ul>
                    <li v-for="error in errors">{{ error }}</li>
                </ul>
            </div>

            <div class="flex flex-wrap items-center w-full sticky" id="publish-controls">

                <h1 class="w-full my-1 text-center lg:text-left lg:flex-1">
                    <span>{{ title }}</span>
                </h1>

                <div class="controls flex flex-wrap items-center w-full lg:w-auto justify-center">

                    <div class="mr-2 my-1 fs-13 opacity-50" v-if="! canEdit">
                        <span class="icon icon-lock"></span>
                        {{ translate('cp.read_only_mode') }}
                    </div>

                    <status-field
                        class="my-1"
                        v-if="shouldShowStatus"
                        :locale="locale"
                        :locales="locales"
                        :allow-statuses="allowStatuses"
                        :status.sync="formData.status"></status-field>

                    <user-options v-if="isUser && !isNew" :username="slug" :status="contentData.status" class="mr-2"></user-options>

                    <div class="btn-group my-1 mr-2" v-if="$parent.isPublishPage && url">
                        <template v-if="staticCachingEnabled">
                            <a :href="url" target="_blank" class="btn">{{ translate('cp.visit_url') }}</a>
                        </template>
                        <template v-else>
                            <button type="button" class="btn" @click.prevent="$parent.preview">{{ translate('cp.sneak_peek') }}</button>
                            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">{{ translate('cp.toggle_dropdown') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a :href="url" target="_blank">{{ translate('cp.visit_url') }}</a></li>
                            </ul>
                        </template>
                    </div>

                    <div class="btn-group btn-group-primary my-1" v-if="canEdit">

                        <button v-if="publishType === 'save'" type="button" class="btn btn-primary" @click="publishWithoutContinuing" :disabled="saving">{{ translate('cp.save') }}</button>
                        <button v-if="publishType === 'continue'" type="button" class="btn btn-primary" @click="publishAndContinue" :disabled="saving">{{ translate('cp.save_and_continue') }}</button>
                        <button v-if="allowSaveAndAddAnother && publishType === 'another'" type="button" class="btn btn-primary" @click="publishAndAnother" :disabled="saving">{{ translate('cp.save_and_another') }}</button>

                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" :disabled="saving">
                            <span class="caret"></span>
                            <span class="sr-only">{{ translate('cp.toggle_dropdown') }}</span>
                        </button>

                        <ul class="dropdown-menu">
                            <li v-if="publishType !== 'continue'"><a id="publish-continue" @click="publishAndContinue">{{ translate('cp.save_and_continue') }}</a></li>
                            <li v-if="publishType !== 'save'"><a @click="publishWithoutContinuing">{{ translate('cp.save') }}</a></li>
                            <li v-if="allowSaveAndAddAnother && publishType !== 'another'"><a @click="publishAndAnother">{{ translate('cp.save_and_another') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>


            <div :class="[ 'w-full', { 'px-1 md:px-3': !isSneakPeeking } ]">

                <div :class="[ 'publish-tabs tabs', { 'mb-2': !isSneakPeeking } ]" v-show="mainSections.length > 1">
                    <a v-for="section in mainSections"
                        :class="{ 'active': activeSection === section.handle, 'has-error': sectionHasError(section.handle) }"
                        @click="activeSection = section.handle"
                        v-text="sectionDisplay(section)">
                    </a>
                </div>

                <div class="flex justify-between">

                    <div class="w-full">
                        <publish-section
                            v-for="(section, i) in mainSections"
                            v-show="activeSection === section.handle"
                            :section="section"
                            :fieldset="fieldset"
                            :errors="errors"
                            :hidden-fields="hiddenFields"
                            :data.sync="formData.fields"
                            :autofocus="i === 0"
                            :meta-fields="metaFields"
                            :env="extra.env"
                        ></publish-section>
                    </div>

                    <div class="publish-sidebar ml-32" v-show="shouldShowSidebar">
                        <publish-section
                            :section="sidebarSection"
                            :fieldset="fieldset"
                            :errors="errors"
                            :hidden-fields="hiddenFields"
                            :data.sync="formData.fields"
                        ></publish-section>
                    </div>

                </div>
            </div>

        </div>
    </div>

</template>

<script>
import moment from 'moment';
import Conditionals from './Conditionals';
import Fieldset from './Fieldset';
Mousetrap = require('mousetrap');

// Mousetrap Bind Global
// (function(a){var c={},d=a.prototype.stopCallback;a.prototype.stopCallback=function(e,b,a,f){return this.paused?!0:c[a]||c[f]?!1:d.call(this,e,b,a)};a.prototype.bindGlobal=function(a,b,d){this.bind(a,b,d);if(a instanceof Array)for(b=0;b<a.length;b++)c[a[b]]=!0;else c[a]=!0};a.init()})(Mousetrap);

export default {

    components: {
        'publish-fields': require('./Fields.vue'),
        'user-options': require('./user-options'),
        'status-field': require('./StatusField.vue'),
        'publish-section': require('./Section.vue')
    },

    mixins: [Conditionals],

    deep: true,

    props: {
        title: String,
        extra: {
            type: String,
            default: '{}'
        },
        isNew: Boolean,
        contentType: String,
        uuid: String,
        fieldsetName: String,
        slug: {
            type: String,
            default: ''
        },
        uri: String,
        url: String,
        submitUrl: String,
        status: {
            type: Boolean,
            default: true
        },
        locale: {
            type: String,
            default: () => Object.keys(Statamic.locales)[0]
        },
        locales: {
            type: String,
            default: '[{}]'
        },
        isDefaultLocale: {
            type: Boolean,
            default: true
        },
        removeTitle: {
            type: Boolean,
            default: false
        },
        readOnly: {
            type: Boolean,
            default: false
        },
        updateTitleOnSave: {
            type: Boolean,
            default: true
        },
        metaFields: {
            type: Boolean,
            default: true
        },
        allowSaveAndAddAnother: {
            type: Boolean,
            default: false
        }
    },

    data: function() {
        return {
            loading: true,
            saving: false,
            fieldset: {},
            contentData: null,
            formData: { extra: {}, fields: {} },
            formDataInitialized: false,
            isSlugModified: false,
            iframeLoading: false,
            previewRequestQueued: false,
            errors: [],
            publishType: 'save',
            staticCachingEnabled: window.Statamic.staticCachingEnabled,
            activeSection: null
        };
    },

    computed: {

        isEntry: function() {
            return this.contentType === 'entry';
        },

        isTaxonomy: function() {
            return this.contentType === 'taxonomy';
        },

        isGlobal: function() {
            return this.contentType === 'global';
        },

        isUser: function() {
            return this.contentType === 'user';
        },

        isSettings: function() {
            return this.contentType === 'settings';
        },

        isAddon: function() {
            return this.contentType === 'addon';
        },

        isPage: function() {
            return this.contentType === 'page';
        },

        isHomePage: function() {
            return this.isPage && this.uri === '/';
        },

        canEdit: function() {
            if (this.readOnly === true) return false;

            if (this.contentType === 'entry') {
                return this.can('collections:'+ this.extra.collection +':edit')
            } else if (this.contentType === 'page') {
                return this.can('pages:edit')
            } else if (this.contentType === 'taxonomy') {
                return this.can('taxonomies:'+ this.extra.taxonomy +':edit')
            } else if (this.contentType === 'global') {
                return this.can('globals:'+ this.slug +':edit')
            } else if (this.contentType === 'user') {
                return Statamic.userId === this.uuid ? true : this.can('users:edit');
            } else if (this.isAddon || this.isSettings) {
                return this.can('super');
            }

            return true;
        },

        shouldShowStatus: function() {
            return !this.isSettings && !this.isAddon && !this.isUser;
        },

        allowStatuses: function () {
            return !this.isTaxonomy && !this.isGlobal && !this.isHomePage;
        },

        shouldShowDate: function() {
            // Only entries can have a date
            if (!this.isEntry) {
                return false;
            }

            // Existing entry and a datetime has been passed in?
            if (!this.isNew && this.formData.fields.date) {
                return true;
            }

            // New entry and it uses dates for ordering?
            if (this.isNew && this.formData.extra.order_type === 'date') {
                return true;
            }

            return false;
        },

        shouldShowSneakPeek: function() {
            return !this.isGlobal && !this.isSettings && !this.isUser && !this.isAddon;
        },

        isSneakPeeking: function() {
            return this.$root.isPreviewing;
        },

        hasErrors: function() {
            return _.size(this.errors) !== 0;
        },

        dateFieldConfig: function () {
            return this.fieldset.date || {};
        },

        filteredFormData() {
            // Make a copy so we don't modify the original formData
            const formData = JSON.parse(JSON.stringify(this.formData));

            // Remove any hidden fields
            formData.fields = _.objReject(formData.fields, (value, key) => this.hiddenFields.includes(key));

            return formData;
        },

        shouldShowSidebar() {
            if (this.sidebarSection.fields.length == 0 || this.$root.isPreviewing || this.$root.windowWidth < 1366) return false;

            return true;
        },

        sections() {
            return this.fieldset.sections;
        },

        mainSections() {
            if (! this.shouldShowSidebar) return this.sections;

            return _.filter(this.sections, section => section.handle != 'sidebar');
        },

        sidebarSection() {
            return _.find(this.sections, { handle: 'sidebar' });
        },

        // A mapping of fields to which section they are in.
        sectionFields() {
            let fields = {};
            this.sections.forEach(section => {
                section.fields.forEach(field => {
                    fields[field.name] = section.handle;
                })
            });
            return fields;
        },

        // A mapping of fields with errors to which section they are in.
        sectionErrors() {
            let errors = {};
            Object.keys(this.errors).forEach(field => {
                field = field.substr(7); // without `fields.` prefix
                errors[field] = this.sectionFields[field];
            });
            return errors;
        },

        // When an error occurs, we will focus on the first section with a field containing an error.
        sectionToFocusOnError() {
            // We want to exclude any fields in the sidebar if it's wide enough to be visible.
            const sections = this.shouldShowSidebar
                ? _.omit(this.sectionErrors, (section) => section === 'sidebar')
                : this.sectionErrors;

            const keys = Object.keys(sections);

            // After excluding the sidebar fields, if there are zero results, it means that the errors
            // are only in the sidebar so we'll continue to show the currently selected section.
            if (keys.length === 0) return this.activeSection;

            return sections[keys[0]];
        },

        saveBehaviorScope() {
            return 'statamic.publish.' + this.contentType + '.type';
        }
    },

    methods: {

        initFormData: function() {
            this.formData = {
                fieldset: null, // overridden in initFieldset
                new: this.isNew,
                type: this.contentType,
                uuid: this.uuid,
                id: this.uuid,
                status: this.status,
                slug: this.contentData.slug,
                locale: this.locale,
                extra: this.extra,
                fields: this.contentData
            };

            this.formDataInitialized = true;
        },

        publish: function() {
            var self = this;

            self.saving = true;
            self.errors = [];

            if (this.submitUrl) {
                var url = this.submitUrl;
            } else if (this.isSettings) {
                var url = cp_url('settings/') + this.slug;
            } else if (this.isAddon) {
                var url = cp_url('addons/') + this.extra.addon + '/settings';
            }

            var request = this.$http.post(url, this.filteredFormData)

            request.success(function(data) {
                self.loading = false;

                if (data.success) {
                    this.$dispatch('changesMade', false);
                    if (data.redirect && (this.publishType !== 'continue' || this.isNew)) {
                        window.location = data.redirect;
                        return;
                    }
                    this.saving = false;
                    if (this.updateTitleOnSave) {
                        this.title = (this.isUser) ? this.formData.fields.username : this.formData.fields.title;
                    }
                    this.$dispatch('setFlashSuccess', data.message, { timeout: 1500 });
                } else {
                    this.$dispatch('setFlashError', translate('cp.error'));
                    this.saving = false;
                    this.errors = data.errors;
                    this.activeSection = this.sectionToFocusOnError;
                    this.$nextTick(() => {
                        $('html, body').animate({ scrollTop: 0 });
                    });
                }
            });

            request.error(response => {
                this.saving = false;
                this.errors = [];
                const key = response.exception === 'TokenMismatchException' ? 'session_expired_error' : 'publish_error';
                this.$notify.error(translate(`cp.${key}`));
            });
        },

        publishWithoutContinuing: function () {
            this.publishType = 'save';
            localStorage.setItem(this.saveBehaviorScope, 'save');
            Vue.delete(this.formData, 'continue');
            Vue.delete(this.formData, 'another');

            this.publish();
        },

        publishAndContinue: function() {
            this.publishType = 'continue';
            localStorage.setItem(this.saveBehaviorScope, 'continue');
            this.formData.continue = true;
            Vue.delete(this.formData, 'another');

            this.publish();
        },

        publishAndAnother: function() {
            this.publishType = 'another';
            localStorage.setItem(this.saveBehaviorScope, 'another');
            this.formData.another = true;
            Vue.delete(this.formData, 'continue');

            this.publish();
        },

        initPreview: function() {
            if (! $('#sneak-peek-iframe').length) {
                $('<iframe frameborder="0" id="sneak-peek-iframe">').appendTo('#sneak-peek');
            }
            this.updatePreview();
        },

        updatePreview: _.debounce(function(e) {
            if (this.iframeLoading) {
                this.previewRequestQueued = true;
                return;
            }

            var formData = this.formData;
            formData['preview'] = true;

            this.iframeLoading = true;

            if (! this.isNew) {
                // existing pages already have a url.
                var url = this.url;
            } else {
                if (this.isPage) {
                    var slug = this.formData.slug || 'new-page';
                    var url = this.extra.parent_url + '/' + slug;
                    url = url.replace('//', '/');
                } else {
                    var url = this.entryUrl();
                }
            }

            this.$http.post(url, formData, function(data, status, request) {
                this.updatePreviewIframe(data);
                this.iframeLoading = false;
                if (this.previewRequestQueued) {
                    this.previewRequestQueued = false;
                    this.updatePreview();
                }
            });
        }, 150),

        updatePreviewIframe: function(data) {
            var $iframe = $('#sneak-peek-iframe');
            var iframe = $iframe.get(0);

            var scrollX = $(iframe.contentWindow.document).scrollLeft();
            var scrollY = $(iframe.contentWindow.document).scrollTop();

            data += '<script type="text/javascript">window.scrollTo('+scrollX+', '+scrollY+');\x3c/script>';

            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(data);
            iframe.contentWindow.document.close();
        },

        syncTitleAndSlugFields: function() {
            if (this.isNew) {
                this.$watch('formData.fields.title', function(title) {
                    if (this.$slugify(title) == this.formData.fields.slug) {
                        this.isSlugModified = false;
                    }

                    if (! this.isSlugModified) {
                        this.formData.fields.slug = this.$slugify(title);
                    }
                });
            }
        },

        entryUrl: function () {
            if (! this.isNew) {
                return this.uri;
            }

            var fallbackSlug = (this.isTaxonomy) ? 'new-term' : 'new-entry';
            var slug = this.formData.slug || fallbackSlug;

            var route = this.extra.route;
            var url = route;

            if (this.extra.order_type === 'date') {
                var date = this.date();
            }

            var re = /{\s*([a-zA-Z0-9_\-]+)\s*}/g;
            var results;
            while ((results = re.exec(route)) !== null) {
                var match = results[0];
                var value = '';

                switch (match) {
                    case '{year}':
                        value = date.format('YYYY');
                        break;
                    case '{month}':
                        value = date.format('MM');
                        break;
                    case '{day}':
                        value = date.format('DD');
                        break;
                    case '{slug}':
                        value = slug;
                        break;
                    default:
                        var field = match.substring(1, match.length-1);
                        value = this.formData.fields[field];
                        break;
                }

                url = url.replace(match, value);
            }

            return url;
        },

        date: function () {
            var date = this.extra.datetime;

            var format = 'YYYY-MM-DD';

            if (date.length > 10) {
                format += ' HH:mm';
            }

            return moment(date, format);
        },

        modifySlug: function (event) {
            var title = this.formData.fields.title;
            var slug  = this.formData.slug;

            this.isSlugModified = (this.$slugify(title) !== slug);
        },

        getInitialPublishType: function () {
            let type = localStorage.getItem(this.saveBehaviorScope) || 'save';

            if (!this.allowSaveAndAddAnother && type === 'another') {
                type = 'save';
            }

            return type;
        },

        getFieldset() {
            if (Statamic.Publish.fieldset) {
                this.initFieldset(Statamic.Publish.fieldset);
                return;
            }

            var params = {};
            var url = cp_url('fieldsets-json/') + this.fieldsetName;

            params.locale = this.locale;
            params.taxonomies = this.isEntry;
            this.$http.get(url, params).success(function(data) {
                this.initFieldset(data);
            });
        },

        initFieldset(data) {
            this.fieldset = new Fieldset(data)
                .showDate(this.shouldShowDate);

            if (this.isPage || this.isEntry || this.isTaxonomy) {
                this.fieldset.showSlug(!this.isHomePage).prependTitle().prependMeta();
            }

            this.activeSection = this.fieldset.sections[0].handle;
            this.initConditions();

            this.formData.fieldset = this.fieldsetName || this.fieldset.name;
        },

        sectionHasError(handle) {
            return _.chain(this.sectionErrors).values().contains(handle).value();
        },

        sectionDisplay(section) {
            return section.display || `${section.handle[0].toUpperCase()}${section.handle.slice(1)}`;
        }
    },

    watch: {

        shouldShowSidebar(shouldShow) {
            // If the sidebar hidden, and it was the active tab, when we show the sidebar
            // we won't have an active tab anymore, so we'll just activate the first.
            // Also, if the first one *is* the sidebar, activate the second one.
            if (shouldShow && this.activeSection === 'sidebar') {
                this.activeSection = this.sections[0].handle;
                if (this.activeSection === 'sidebar') {
                    this.activeSection = this.sections[1].handle;
                }
            }
        },

        activeSection(section) {
            // Allow things to track when the seciton is changed.
            // For example, the Grid fieldtype may need to recalculate its container width.
            this.$root.$emit('publish.section.changed', section);
        }

    },

    mounted() {
        var self = this;

        this.extra = JSON.parse(this.extra);
        this.contentData = JSON.parse(JSON.stringify(Statamic.Publish.contentData));

        if (this.locales) {
            this.locales = JSON.parse(this.locales);
        }

        this.initFormData();
        this.getFieldset();

        this.publishType = this.getInitialPublishType();
        if (this.publishType === 'continue') {
            this.formData.continue = true;
        } else if (this.publishType === 'another') {
            this.formData.another = true;
        }

        this.syncTitleAndSlugFields();

        var sneakPeekWatcher = null;
        if (this.shouldShowSneakPeek) {
            this.$root.isPublishPage = true;

            // We've initated Live Preview Mode
            this.$on('previewing', function() {
                this.initPreview();

                sneakPeekWatcher = this.$watch('formData', function(newVal) {
                    this.updatePreview();
                }, { deep: true });
            });
        }

        this.$on('previewing.stopped', function() {
            // The watcher returns a method to stop itself.
            sneakPeekWatcher();
        });

        this.$on('fieldsetLoaded', function(fieldset) {
            this.fieldset = fieldset;
            this.loading = false;
        });

        if (this.canEdit) {
            Mousetrap.bindGlobal('mod+s', function(e) {
                e.preventDefault();
                self.publishAndContinue();
            });

            Mousetrap.bindGlobal('meta+enter', function(e) {
                e.preventDefault();
                self.publish();
            });
        }
    }

};
</script>
