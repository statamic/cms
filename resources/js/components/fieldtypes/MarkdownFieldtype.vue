<template>
    <div class="markdown-fieldtype-wrapper" :class="{'markdown-fullscreen': fullScreenMode, 'markdown-dark-mode': darkMode }">

        <uploader
            ref="uploader"
            :enabled="assetsEnabled"
            :container="container"
            :path="folder"
            @updated="uploadsUpdated"
            @upload-complete="uploadComplete"
        >
            <div slot-scope="{ dragging }">
                <div class="markdown-toolbar">
                    <div class="markdown-modes">
                        <button @click="mode = 'write'" :class="{ 'active': mode == 'write' }" v-text=" __('Write')" :aria-pressed="mode === 'write' ? 'true' : 'false'" />
                        <button @click="mode = 'preview'" :class="{ 'active': mode == 'preview' }" v-text=" __('Preview')" :aria-pressed="mode === 'preview' ? 'true' : 'false'" />
                    </div>

                    <div class="markdown-buttons" v-if="! isReadOnly">
                        <button @click="bold" v-tooltip="__('Bold')" :aria-label="__('Bold')">
                            <i class="fa fa-bold"></i>
                        </button>
                        <button @click="italic" v-tooltip="__('Italic')" :aria-label="__('Italic')">
                            <i class="fa fa-italic"></i>
                        </button>
                        <button @click="insertLink('')" v-tooltip="__('Insert Link')" :aria-label="__('Insert Link')">
                            <i class="fa fa-link"></i>
                        </button>
                        <button @click="addAsset" v-if="assetsEnabled" v-tooltip="__('Insert Asset')" :aria-label="__('Insert Asset')">
                            <i class="fa fa-picture-o"></i>
                        </button>
                        <button @click="insertImage('')" v-else v-tooltip="__('Insert Image')" :aria-label="__('Insert Image')">
                            <i class="fa fa-picture-o"></i>
                        </button>
                        <button @click="toggleDarkMode" v-tooltip="darkMode ? __('Light Mode') : __('Dark Mode')" :aria-label="__('Toggle Dark Mode')" v-if="fullScreenMode">
                            <svg-icon name="dark-mode" class="w-4 h-4" />
                        </button>
                        <button @click="openFullScreen" v-tooltip="__('Fullscreen Mode')" :aria-label="__('Fullscreen Mode')" v-if="! fullScreenMode">
                            <svg-icon name="expand" class="w-4 h-4" />
                        </button>
                        <button @click="closeFullScreen" v-tooltip="__('Close Fullscreen Mode')" :aria-label="__('Close Fullscreen Mode')" v-if="fullScreenMode">
                            <svg-icon name="shrink-all" class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <div class="drag-notification" v-show="dragging">
                    <svg-icon name="upload" class="h-12 w-12 mb-2" />
                    {{ __('Drop File to Upload') }}
                </div>

                <uploads
                    v-if="uploads.length"
                    :uploads="uploads"
                    class="-mt-px"
                />

                <div :class="`mode-wrap mode-${mode}`" @click="focus">
                    <div class="markdown-writer"
                        ref="writer"
                        v-show="mode == 'write'"
                        @dragover="draggingFile = true"
                        @dragleave="draggingFile = false"
                        @drop="draggingFile = false"
                        @keydown="shortcut">

                        <div class="editor" ref="codemirror"></div>

                        <div class="helpers">
                            <div class="flex w-full">
                                <div class="markdown-cheatsheet-helper">
                                    <button class="text-link flex items-center" @click="showCheatsheet = true" :aria-label="__('Show Markdown Cheatsheet')">
                                        <svg-icon name="markdown-icon" class="w-6 h-4 items-start mr-1" />
                                        <span>{{ __('Markdown Cheatsheet') }}</span>
                                    </button>
                                </div>
                            </div>
                            <div v-if="fullScreenMode" class="flex items-center pr-1">
                                <div class="whitespace-no-wrap mr-2"><span v-text="count.words" /> {{ __('Words') }}</div>
                                <div class="whitespace-no-wrap"><span v-text="count.characters" /> {{ __('Characters') }}</div>
                            </div>
                        </div>

                        <div class="drag-notification" v-if="assetsEnabled && draggingFile">
                            <svg-icon name="upload" class="h-12 w-12 mb-2" />
                            {{ __('Drop File to Upload') }}
                        </div>
                    </div>

                    <div v-show="mode == 'preview'" v-html="markdownPreviewText" class="markdown-preview clean-content"></div>
                </div>
            </div>
        </uploader>

        <stack v-if="showAssetSelector && ! isReadOnly" name="markdown-asset-selector" @closed="closeAssetSelector">
            <selector
                  :container="container"
                  :folder="folder"
                  :selected="selectedAssets"
                  :restrict-container-navigation="restrictAssetNavigation"
                  :restrict-folder-navigation="restrictAssetNavigation"
                  @selected="assetsSelected"
                  @closed="closeAssetSelector"
            />
        </stack>

        <stack name="markdownCheatSheet" v-if="showCheatsheet" @closed="showCheatsheet = false">
            <div class="h-full overflow-auto p-3 bg-white relative">
                <button class="btn-close absolute top-0 right-0 mt-2 mr-4" @click="showCheatsheet = false" :aria-label="__('Close Markdown Cheatsheet')">&times;</button>
                <div class="max-w-md mx-auto my-4 clean-content">
                    <h2 v-text="__('Markdown Cheatsheet')"></h2>
                    <div v-html="__('markdown.cheatsheet')"></div>
                </div>
            </div>
        </stack>

        <vue-countable :text="data" :elementId="'myId'" @change="change"></vue-countable>

    </div>
</template>

<script>
var CodeMirror = require('codemirror');
var { marked } = require('marked');
var PlainTextRenderer = require('marked-plaintext');

require('codemirror/addon/edit/closebrackets');
require('codemirror/addon/edit/matchbrackets');
require('codemirror/addon/display/autorefresh');
require('codemirror/mode/htmlmixed/htmlmixed');
require('codemirror/mode/xml/xml');
require('codemirror/mode/markdown/markdown');
require('codemirror/mode/gfm/gfm');
require('codemirror/mode/javascript/javascript');
require('codemirror/mode/css/css');
require('codemirror/mode/clike/clike');
require('codemirror/mode/php/php');
require('codemirror/mode/yaml/yaml');
require('codemirror/addon/edit/continuelist');

import Selector from '../assets/Selector.vue';
import Uploader from '../assets/Uploader.vue';
import Uploads from '../assets/Uploads.vue';
import VueCountable from 'vue-countable'

// Keymaps
import 'codemirror/keymap/sublime'

export default {

    mixins: [Fieldtype],

    components: {
        Selector,
        Uploader,
        Uploads,
        VueCountable
    },

    data: function() {
        return {
            data: this.value || '',
            mode: 'write',
            selections: null,      // CodeMirror text selections
            showAssetSelector: false,  // Is the asset selector opened?
            selectedAssets: [],    // Assets selected in the selector
            selectorViewMode: null,
            draggingFile: false,
            showCheatsheet: false,
            fullScreenMode: false,
            darkMode: false,
            codemirror: null,       // The CodeMirror instance
            uploads: [],
            count: {},
            escBinding: null,
            markdownPreviewText: null
        };
    },

    watch: {

        data(data) {
            this.updateDebounced(data);
        },

        fullScreenMode: {
            immediate: true,
            handler: fullscreen => {
                if (fullscreen) {
                    document.body.style.setProperty("overflow", "hidden")
                } else {
                    document.body.style.removeProperty("overflow")
                }
            }
        },

        mode(mode) {
            if (mode === 'preview') this.updateMarkdownPreview();
        }

    },

    methods: {

        closeFullScreen() {
            this.fullScreenMode = false;
            this.escBinding.destroy();
            this.trackHeightUpdates();
        },

        openFullScreen() {
            this.fullScreenMode = true;
            this.escBinding = this.$keys.bindGlobal('esc', this.closeFullScreen);
            this.trackHeightUpdates();
        },

        toggleDarkMode() {
            this.darkMode = ! this.darkMode;
        },

        /**
         * Get the text for a selection
         *
         * @param  Range selection  A CodeMirror Range
         * @return string
         */
        getText: function(selection) {
            var i = _.indexOf(this.selections, selection);

            return this.codemirror.getSelections()[i];
        },

        /**
         * Inserts an image at the selection
         *
         * @param  String url  URL of the image
         * @param  String alt  Alt text
         */
        insertImage: function(url, alt) {
            var cm = this.codemirror.doc

            var selection = '';
            if (cm.somethingSelected()) {
                selection = cm.getSelection();
            } else if (alt) {
                selection = alt;
            }

            var url = url || '';

            // Replace the string
            var str = '![' + selection + ']('+ url +')';

            cm.replaceSelection(str, 'start');
            // Select the text
            var line = cm.getCursor().line;
            var start = cm.getCursor().ch + 2; // move past the ![
            var end = start + selection.length;
            cm.setSelection({line:line,ch:start}, {line:line,ch:end});

            this.codemirror.focus();
        },

        /**
         * Appends an image to the end of the data
         *
         * @param  String url  URL of the image
         * @param  String alt  Alt text
         */
        appendImage: function(url, alt) {
            alt = alt || '';
            this.data += '\n\n!['+alt+']('+url+')';
        },

        /**
         * Inserts a link at the selection
         *
         * @param  String url   URL of the link
         * @param  String text  Link text
         */
        insertLink: function(url, text) {
            var cm = this.codemirror.doc

            var selection = '';
            if (cm.somethingSelected()) {
                selection = cm.getSelection();
            } else if (text) {
                selection = text;
            }

            if (! url) {
                url = prompt(__('Enter URL'), 'https://');
                if (! url) {
                    url = '';
                }
            }

            // Replace the string
            var str = '[' + selection + ']('+ url +')';
            cm.replaceSelection(str, 'start');

            // Select the text
            var line = cm.getCursor().line;
            var start = cm.getCursor().ch + 1; // move past the first [
            var end = start + selection.length;
            cm.setSelection({line:line,ch:start}, {line:line,ch:end});

            this.codemirror.focus();
        },

        /**
         * Inserts a link at the end of the data
         *
         * @param  String url   URL of the link
         * @param  String text  Link text
         */
        appendLink: function(url, text) {
            text = text || '';
            this.data += '\n\n['+text+']('+url+')';
        },

        /**
         * Toggle the boldness on the current selection(s)
         */
        bold: function() {
            var self = this;
            var replacements = [];

            _.each(self.selections, function (selection, i) {
                var replacement = (self.isBold(selection))
                    ? self.removeBold(selection)
                    : self.makeBold(selection);

                replacements.push(replacement);
            });

            this.codemirror.replaceSelections(replacements, 'around');

            this.codemirror.focus();
        },

        /**
         * Check if a string is bold
         *
         * @param  Range  selection  CodeMirror selection
         * @return Boolean
         */
        isBold: function (selection) {
            return this.getText(selection).match(/^\*{2}(.*)\*{2}$/);
        },

        /**
         * Make a string bold
         *
         * @param  Range  selection  CodeMirror selection
         * @return String
         */
        makeBold: function (selection) {
            return '**' + this.getText(selection) + '**';
        },

        /**
         * Remove the boldness from a string
         *
         * @param  Range  selection  CodeMirror selection
         * @return String
         */
        removeBold: function (selection) {
            var text = this.getText(selection);

            return text.substring(2, text.length-2);
        },

        /**
         * Toggle the italics on the current selection(s)
         */
        italic: function() {
            var self = this;
            var replacements = [];

            _.each(self.selections, function (selection, i) {
                var replacement = (self.isItalic(selection))
                    ? self.removeItalic(selection)
                    : self.makeItalic(selection);

                replacements.push(replacement);
            });

            this.codemirror.replaceSelections(replacements, 'around');

            this.codemirror.focus();
        },

        /**
         * Check if a string is italic
         *
         * @param  Range  selection  CodeMirror selection
         * @return Boolean
         */
        isItalic: function (selection) {
            return this.getText(selection).match(/^\_(.*)\_$/);
        },

        /**
         * Make a string italic
         *
         * @param  Range  selection  CodeMirror selection
         * @return String
         */
        makeItalic: function (selection) {
            return '_' + this.getText(selection) + '_';
        },

        /**
         * Remove the italics from a string
         *
         * @param  Range  selection  CodeMirror selection
         * @return String
         */
        removeItalic: function (selection) {
            var text = this.getText(selection);

            return text.substring(1, text.length-1);
        },

        /**
         * Open the asset selector
         */
        addAsset: function() {
            this.showAssetSelector = true;
        },

        /**
         * Execute a keyboard shortcut, when applicable
         */
        shortcut: function(e) {
            var key = e.keyCode;
            var meta = e.metaKey === true;

            if (meta && key === 66) { // cmd+b
                this.bold();
                e.preventDefault();
            }

            if (meta && key === 73) { // cmd+i
                this.italic();
                e.preventDefault();
            }

            if (meta && key === 75) { // cmd+k
                this.insertLink();
                e.preventDefault();
            }
        },

        /**
         * When assets are selected from the modal, this event gets fired.
         *
         * @param  Array assets  All the assets that were selected
         */
        assetsSelected: function (assets) {
            // If one asset is chosen, it's safe to replace the selection.
            // Otherwise we'll just tack on the assets to the end of the text.
            var method = (assets.length === 1) ? 'insert' : 'append';

            this.closeAssetSelector();

            // We don't want to maintain the asset selections
            this.selectedAssets = [];

            this.$axios.get(cp_url('assets-fieldtype'), { params: { assets } }).then(response => {
                _(response.data).each((asset) => {
                    var alt = asset.values.alt || '';
                    var url = encodeURI('statamic://'+asset.reference);
                    if (asset.isImage) {
                        this[method+'Image'](url, alt);
                    } else {
                        this[method+'Link'](url, alt);
                    }
                });
            });
        },

        closeAssetSelector() {
            this.showAssetSelector = false;
        },

        uploadsUpdated(uploads) {
            this.uploads = uploads;
        },

        uploadComplete(upload, uploads) {
            if (upload.is_image) {
                this.insertImage(upload.url);
            } else {
                this.insertLink(upload.url);
            }

            // If there are more uploads in the queue, move the cursor to the
            // end of the document so the selection doesn't get re-replaced.
            if (uploads.length > 1) {
                this.codemirror.setCursor(this.codemirror.lineCount(), 0);
            }
        },

        focus() {
            this.codemirror.focus();
        },

        change(event) {
            this.count = event;
        },

        trackHeightUpdates() {
            const update = () => { window.dispatchEvent(new Event('resize')) };
            const throttled = _.throttle(update, 100);

            this.$root.$on('livepreview.opened', throttled);
            this.$root.$on('livepreview.closed', throttled);
            this.$root.$on('livepreview.resizing', throttled);

            this.$once('hook:beforeDestroy', () => {
                this.$root.$off('livepreview.opened', throttled);
                this.$root.$off('livepreview.closed', throttled);
                this.$root.$off('livepreview.resizing', throttled);
            });
        }

    },

    computed: {
        assetsEnabled: function() {
            return Boolean(this.config && this.config.container);
        },

        container: function() {
            return this.config.container;
        },

        folder: function() {
            return this.config.folder || '/';
        },

        restrictAssetNavigation() {
            return this.config.restrict_assets || false;
        },

        replicatorPreview() {
            return marked(this.data || '', { renderer: new PlainTextRenderer })
                .replace(/<\/?[^>]+(>|$)/g, "");
        },

        updateMarkdownPreview() {
            this.$axios
                .post(this.meta.previewUrl, { value: this.data, config: this.config })
                .then(response => this.markdownPreviewText = response.data)
                .catch(e => this.$toast.error(e.response ? e.response.data.message : __('Something went wrong')));
        }
    },

    mounted() {
        var self = this;

        self.codemirror = CodeMirror(this.$refs.codemirror, {
            value: self.data,
            mode: 'gfm',
            dragDrop: false,
            keyMap: 'sublime',
            direction: document.querySelector('html').getAttribute('dir') ?? 'ltr',
            lineWrapping: true,
            viewportMargin: Infinity,
            tabindex: 0,
            autoRefresh: true,
            readOnly: self.isReadOnly ? 'nocursor' : false,
            inputStyle: 'contenteditable',
            spellcheck: true,
            extraKeys: {
                "Enter": "newlineAndIndentContinueMarkdownList",
                "Cmd-Left": "goLineLeftSmart"
            }
        });

        self.codemirror.on('change', function (cm) {
            self.data = cm.doc.getValue();
        });

        self.codemirror.on('focus', () => self.$emit('focus'));
        self.codemirror.on('blur', () => self.$emit('blur'));

        // Expose the array of selections to the Vue instance
        self.codemirror.on('beforeSelectionChange', function (cm, obj) {
            self.selections = obj.ranges;
        });

        // Update CodeMirror if we change the value independent of CodeMirror
        this.$watch('value', function(val) {
            if (val !== self.codemirror.doc.getValue()) {
                self.codemirror.doc.setValue(val);
            }
        });

        this.trackHeightUpdates();

    }

};
</script>
