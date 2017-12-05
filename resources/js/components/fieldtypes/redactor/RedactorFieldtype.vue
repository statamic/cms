<template>
    <div class="redactor-fieldtype-wrapper">
        <textarea v-el:redactor :name="name" v-model="data"></textarea>
        <selector v-if="showAssetSelector"
                  :container="container"
                  :folder="folder"
                  :selected="selectedAssets"
                  :restrict-navigation="restrictAssetNavigation"
                  @selected="assetsSelected"
                  @closed="closeAssetSelector"
        ></selector>
    </div>
</template>

<script>
module.exports = {

    components: {
        selector: require('../../assets/Selector.vue')
    },

    mixins: [Fieldtype],

    data: function() {
        return {
            mode: 'write',
            showAssetSelector: false,
            selectedAssets: [],
            selectorViewMode: null
        }
    },

    methods: {
        update: function(html) {
            this.data = html;
        },

        addAsset: function() {
            this.showAssetSelector = true
        },

        insertLink: function(url, text) {
            const selection = $(this.$els.redactor).redactor('selection.getHtml');

            if (selection) {
                text = selection;
            }

            $(this.$els.redactor).redactor(
                'insert.html',
                '<a href="' + url + '">' + text + '</a>'
            );
        },

        insertImage: function(url, text) {
            $(this.$els.redactor).redactor(
                'insert.html',
                '<img src="' + url + '" alt="' + text + '" />'
            );
        },

        appendImage: function(url, text) {
            var $r = $(this.$els.redactor);

            var code = $r.redactor('code.get');

            $r.redactor(
                'code.set',
                code + '<img src="' + url + '" alt="' + text + '" />'
            );
        },

        appendLink: function(url, text) {
            var $r = $(this.$els.redactor);

            var code = $r.redactor('code.get');

            $r.redactor(
                'code.set',
                code + '<a href="' + url + '">' + text + '</a>'
            );
        },

        assetsSelected(assets) {
            var self = this;
            var $r = $(self.$els.redactor);
            var selection = $r.redactor('selection.getHtml');
            var code = $r.redactor('code.get');

            if (assets.length > 1) {
                $r.redactor('focus.setEnd');
            }

            this.$http.post(cp_url('assets/get'), { assets }, (response) => {
                _(response).each((asset) => {
                    var url = asset.url;
                    var alt = asset.alt || '';

                    if (assets.length === 1) {
                        var text = selection || alt;
                        if (asset.is_image) {
                            self.insertImage(url, text);
                        } else {
                            self.insertLink(url, text);
                        }
                    } else {
                        if (asset.is_image) {
                            code += '<img src="' + url + '" alt="' + alt + '" />';
                        } else {
                            code += '<a href="' + url + '">' + alt + '</a>';
                        }
                        $r.redactor('code.set', code);
                    }
                });
            });

            this.closeAssetSelector();

            // We don't want to maintain the asset selections
            this.selectedAssets = [];
        },

        closeAssetSelector() {
            this.showAssetSelector = false;
        },

        getReplicatorPreviewText() {
            if (! this.data) return '';

            return $(this.$els.redactor)
                .redactor('clean.getTextFromHtml', this.data)
                .replace(/\n/g, ' ');
        },

        focus() {
            $(this.$els.redactor).redactor('focus.setEnd');
        }
    },

    computed: {
        assetsEnabled: function() {
            return this.config && typeof this.config.container !== 'undefined';
        },

        container: function() {
            return this.config.container;
        },

        folder: function() {
            return this.config.folder || '/';
        },

        restrictAssetNavigation() {
            return this.config.restrict_assets || false;
        }
    },

    ready: function() {
        this.selectorViewMode = Cookies.get('statamic.assets.listing_view_mode') || 'grid';

        var womp = this;

        var defaults = {
            minHeight: 250,
            changeCallback: function () {
                womp.update(this.code.get());
            }
        };

        if (this.config.settings && typeof this.config.settings !== 'string') {
            console.warn('Redactor Fieldtype: You must reference the settings name instead of adding them inline.')
        }

        // Get the appropriate configuration. If the one they've requested
        // doesnt exist, we'll use the first one defined.
        if (_.has(Statamic.redactorSettings, this.config.settings)) {
            var config = Statamic.redactorSettings[this.config.settings];
        } else {
            var config = Statamic.redactorSettings[_.first(_.keys(Statamic.redactorSettings))];
        }

        var settings = _.extend(defaults, config);

        settings.plugins = settings.plugins || [];

        if (this.assetsEnabled) {
            settings.plugins.push('assets');
        }

        $(this.$els.redactor).redactor(settings);
    }
};
</script>
