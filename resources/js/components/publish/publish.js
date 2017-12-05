Mousetrap = require('mousetrap');

// Mousetrap Bind Global
(function(a){var c={},d=a.prototype.stopCallback;a.prototype.stopCallback=function(e,b,a,f){return this.paused?!0:c[a]||c[f]?!1:d.call(this,e,b,a)};a.prototype.bindGlobal=function(a,b,d){this.bind(a,b,d);if(a instanceof Array)for(b=0;b<a.length;b++)c[a[b]]=!0;else c[a]=!0};a.init()})(Mousetrap);

module.exports = {

    template: require('./publish.template.html'),

    components: {
        'publish-fields': require('./fields'),
        'user-options': require('./user-options'),
        'taxonomy-fields': require('./TaxonomyFields.vue'),
        'status-field': require('./StatusField.vue')
    },

    deep: true,

    props: {
        title: String,
        extra: String,
        isNew: Boolean,
        contentType: String,
        titleDisplayName: {
            type: String,
            default: translate('cp.title')
        },
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
        locale: String,
        locales: String,
        isDefaultLocale: {
            type: Boolean,
            default: true
        },
        removeTitle: {
            type: Boolean,
            default: false
        }
    },

    data: function() {
        return {
            loading: false,
            saving: false,
            editingLayout: false,
            fieldset: {},
            contentData: null,
            taxonomies: null,
            formData: { extra: {}, fields: {} },
            formDataInitialized: false,
            isSlugModified: false,
            iframeLoading: false,
            previewRequestQueued: false,
            errors: [],
            continuing: false
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

        shouldShowMeta: function() {
            if (! this.formDataInitialized) return false;

            if (this.isUser && this.shouldShowTaxonomies) {
                return true;
            }

            if ((this.isGlobal || this.isHomePage) && this.locales.length > 1) {
                return true;
            }

            return !this.isSettings && !this.isAddon && (this.shouldShowSlug || this.shouldShowDate);
        },

        shouldShowTitle: function() {
            return !this.isSettings && !this.isAddon && !this.isGlobal && !this.isUser;
        },

        shouldShowSlug: function() {
            return !this.isSettings && !this.isAddon && !this.isGlobal && !this.isUser && !this.isHomePage;
        },

        shouldShowStatus: function() {
            return !this.isSettings && !this.isAddon && !this.isTaxonomy && !this.isUser && !this.isHomePage;
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
            if (!this.isNew && this.formData.extra.datetime) {
                return true;
            }

            // New entry and it uses dates for ordering?
            if (this.isNew && this.formData.extra.order_type === 'date') {
                return true;
            }

            return false;
        },

        shouldShowTaxonomies: function() {
            if (typeof this.taxonomies === 'string') {
                return false;
            }

            // Taxonomy logic for users and pages is backwards.
            // Usually, if no taxonomies are specified in the fieldset, we'll show them all.
            // However, for users and pages, we only want to show taxonomies if they are
            // defined, since taxonomizing users is a pretty uncommon thing to do.
            if ((this.isUser || this.isPage) && !this.fieldset.taxonomies) {
                return false;
            }

            return true;
        },

        shouldShowSneakPeek: function() {
            return !this.isGlobal && !this.isSettings && !this.isUser && !this.isAddon && !this.editingLayout;
        },

        canEditLayout: function() {
            return !this.isSettings && this.can('fieldsets:manage');
        },

        hasErrors: function() {
            return _.size(this.errors) !== 0;
        },

        hasAnyMetaData: function () {
            return this.shouldShowTitle || this.shouldShowSlug || this.shouldShowDate || this.shouldShowStatus;
        },

    },

    methods: {

        initFormData: function() {
            this.formData = {
                fieldset: this.fieldsetName,
                new: this.isNew,
                type: this.contentType,
                uuid: this.uuid,
                status: this.status,
                slug: this.contentData.slug || this.slug,
                locale: this.locale,
                extra: this.extra,
                fields: this.contentData
            };

            this.formDataInitialized = true;
        },

        getFilteredFormData() {
            // Make a copy so we don't modify the original formData
            const formData = JSON.parse(JSON.stringify(this.formData));

            const raw = formData.fields;
            const vm = this.$refs.publishFields;
            const fields = vm.fields;

            let allowed = Object.keys(raw).filter(name => {
                const field = _.findWhere(fields, { name });

                // Fields that aren't in the fieldset (like title) should be
                // included since we would have explicitly added them.
                if (! field) return true;

                return vm.peekaboo(field);
            });

            formData.fields = Object.keys(raw)
                .filter(key => allowed.includes(key))
                .reduce((obj, key) => {
                    obj[key] = raw[key];
                    return obj;
                }, {});

            return formData;
        },

        publish: function() {
            var self = this;

            self.saving = true;
            self.errors = [];

            if (this.isSettings) {
                var url = cp_url('settings/') + this.slug;
            } else if (this.isAddon) {
                var url = cp_url('addons/') + this.extra.addon + '/settings';
            } else {
                var url = this.submitUrl;
            }

            var request = this.$http.post(url, this.getFilteredFormData())

            request.success(function(data) {
                self.loading = false;

                if (data.success) {
                    this.$dispatch('changesMade', false);
                    if (! this.formData.continue || this.isNew) {
                        window.location = data.redirect;
                        return;
                    }
                    this.continuing = true;
                    this.formData.continue = null;
                    this.saving = false;
                    this.title = this.formData.fields.title;
                    this.$dispatch('setFlashSuccess', data.message, { timeout: 1500 });
                } else {
                    this.$dispatch('setFlashError', translate('cp.error'));
                    this.saving = false;
                    this.errors = data.errors;
                    $('html, body').animate({ scrollTop: 0 });
                }
            });

            request.error(function(data) {
                alert('There was a problem saving the data. Please check your logs.');
            });
        },

        publishWithoutContinuing: function () {
            localStorage.setItem('statamic.publish.continue', false);

            this.publish();
        },

        publishAndContinue: function() {
            this.continuing = true;
            this.formData.continue = true;
            localStorage.setItem('statamic.publish.continue', true);

            this.publish();
        },

        editLayout: function(status) {
            this.$event.preventDefault();
            this.editingLayout = status;
        },

        /**
         * Trigger saving of the fieldset layout.
         */
        saveLayout: function() {
            // This will get picked up by the child `publish-fieldset` component.
            this.$broadcast('saveLayout');

            this.editingLayout = false;
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
                    if (this.$slugify(title) == this.formData.slug) {
                        this.isSlugModified = false;
                    }

                    if (! this.isSlugModified) {
                        this.formData.slug = this.$slugify(title);
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

        getInitialContinue: function () {
            return localStorage.getItem('statamic.publish.continue') === 'true';
        }

    },

    ready: function() {
        var self = this;

        this.extra = JSON.parse(this.extra);
        this.contentData = JSON.parse(JSON.stringify(Statamic.Publish.contentData));

        if (Statamic.Publish.taxonomies) {
            this.taxonomies = JSON.parse(JSON.stringify(Statamic.Publish.taxonomies));
        }

        if (this.locales) {
            this.locales = JSON.parse(this.locales);
        }

        this.continuing = this.getInitialContinue();

        this.initFormData();

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
        });

        Mousetrap.bindGlobal('mod+s', function(e) {
            e.preventDefault();
            self.publishAndContinue();
        });

        Mousetrap.bindGlobal(['meta+enter','meta+return'], function(e) {
            e.preventDefault();
            self.publish();
        });
    }

};
