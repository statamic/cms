<template>
<portal name="markdown-fullscreen" :disabled="!fullScreenMode" target-class="markdown-fieldtype">
<element-container @resized="refresh">
    <div class="markdown-fieldtype-wrapper @container/markdown" :class="{'markdown-fullscreen': fullScreenMode, 'markdown-dark-mode': darkMode }">

        <uploader
            ref="uploader"
            :enabled="assetsEnabled"
            :container="container"
            :path="folder"
            @updated="uploadsUpdated"
            @upload-complete="uploadComplete"
        >
            <div slot-scope="{ dragging }">

                <publish-field-fullscreen-header
                    v-if="fullScreenMode"
                    :title="config.display"
                    :field-actions="fieldActions"
                    @close="toggleFullscreen">
                    <div class="markdown-toolbar">
                        <div class="markdown-modes">
                            <button @click="mode = 'write'" :class="{ 'active': mode == 'write' }" v-text=" __('Write')" :aria-pressed="mode === 'write' ? 'true' : 'false'" />
                            <button @click="mode = 'preview'" :class="{ 'active': mode == 'preview' }" v-text=" __('Preview')" :aria-pressed="mode === 'preview' ? 'true' : 'false'" />
                        </div>
                        <div class="markdown-buttons" v-if="! isReadOnly">
                            <button
                                v-for="button in buttons"
                                v-tooltip="button.text"
                                :aria-label="button.text"
                                @click="button.command(editor)"
                            >
                                <svg-icon :name="button.svg" class="w-4 h-4" />
                            </button>
                            <button @click="toggleDarkMode" v-tooltip="darkMode ? __('Light Mode') : __('Dark Mode')" :aria-label="__('Toggle Dark Mode')" v-if="fullScreenMode">
                                <svg-icon name="dark-mode" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </publish-field-fullscreen-header>

                <div class="markdown-toolbar" v-if="!fullScreenMode">
                    <div class="markdown-modes">
                        <button @click="mode = 'write'" :class="{ 'active': mode == 'write' }" v-text=" __('Write')" :aria-pressed="mode === 'write' ? 'true' : 'false'" />
                        <button @click="mode = 'preview'" :class="{ 'active': mode == 'preview' }" v-text=" __('Preview')" :aria-pressed="mode === 'preview' ? 'true' : 'false'" />
                    </div>
                    <div class="markdown-buttons" v-if="! isReadOnly">
                        <button
                            v-for="button in buttons"
                            v-tooltip="button.text"
                            :aria-label="button.text"
                            @click="button.command(editor)"
                        >
                            <svg-icon :name="button.svg" class="w-4 h-4" />
                        </button>
                        <button @click="toggleDarkMode" v-tooltip="darkMode ? __('Light Mode') : __('Dark Mode')" :aria-label="__('Toggle Dark Mode')" v-if="fullScreenMode">
                            <svg-icon name="dark-mode" class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <div class="drag-notification" v-show="dragging">
                    <svg-icon name="upload" class="h-12 w-12 mb-4" />
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
                                        <svg-icon name="markdown-icon" class="w-6 h-4 items-start rtl:ml-2 ltr:mr-2" />
                                        <span>{{ __('Markdown Cheatsheet') }}</span>
                                    </button>
                                </div>
                            </div>
                            <div v-if="fullScreenMode" class="flex items-center rtl:pl-2 ltr:pr-2">
                                <div class="whitespace-nowrap rtl:ml-4 ltr:mr-4"><span v-text="count.words" /> {{ __('Words') }}</div>
                                <div class="whitespace-nowrap"><span v-text="count.characters" /> {{ __('Characters') }}</div>
                            </div>
                        </div>

                        <div class="drag-notification" v-if="assetsEnabled && draggingFile">
                            <svg-icon name="upload" class="h-12 w-12 mb-4" />
                            {{ __('Drop File to Upload') }}
                        </div>
                    </div>

                    <div v-show="mode == 'preview'" v-html="markdownPreviewText" class="markdown-preview prose-sm @md/markdown:prose-base"></div>
                </div>
            </div>
        </uploader>

        <stack v-if="showAssetSelector && ! isReadOnly" name="markdown-asset-selector" @closed="closeAssetSelector">
            <selector
                  :container="container"
                  :folder="folder"
                  :selected="selectedAssets"
                  :restrict-folder-navigation="restrictAssetNavigation"
                  @selected="assetsSelected"
                  @closed="closeAssetSelector"
            />
        </stack>

        <stack name="markdownCheatSheet" v-if="showCheatsheet" @closed="showCheatsheet = false">
            <div class="h-full overflow-auto p-6 bg-white dark:bg-dark-600 relative">
                <button class="btn-close absolute top-0 rtl:left-0 ltr:right-0 mt-4 rtl:ml-8 ltr:mr-8" @click="showCheatsheet = false" :aria-label="__('Close Markdown Cheatsheet')">&times;</button>
                <div class="max-w-md mx-auto my-8 prose">
                    <h2 v-text="__('Markdown Cheatsheet')"></h2>
                    <div v-html="__('markdown.cheatsheet')"></div>
                </div>
            </div>
        </stack>

    </div>
</element-container>
</portal>
</template>

<script>
import { marked } from 'marked';
import PlainTextRenderer from 'marked-plaintext';

import CodeMirror from 'codemirror/lib/codemirror';
import 'codemirror/addon/edit/closebrackets';
import 'codemirror/addon/edit/matchbrackets';
import 'codemirror/addon/display/autorefresh';
import 'codemirror/mode/htmlmixed/htmlmixed';
import 'codemirror/mode/xml/xml';
import 'codemirror/mode/markdown/markdown';
import 'codemirror/mode/gfm/gfm';
import 'codemirror/mode/javascript/javascript';
import 'codemirror/mode/css/css';
import 'codemirror/mode/clike/clike';
import 'codemirror/mode/php/php';
import 'codemirror/mode/yaml/yaml';
import 'codemirror/addon/edit/continuelist';

import { availableButtons } from './buttons';
import Selector from '../../assets/Selector.vue';
import Uploader from '../../assets/Uploader.vue';
import Uploads from '../../assets/Uploads.vue';
// Keymaps
import 'codemirror/keymap/sublime'

/**
 * `ucs2decode` function from the punycode.js library.
 *
 * Creates an array containing the decimal code points of each Unicode
 * character in the string. While JavaScript uses UCS-2 internally, this
 * function will convert a pair of surrogate halves (each of which UCS-2
 * exposes as separate characters) into a single code point, matching
 * UTF-16.
 *
 * @see     <http://goo.gl/8M09r>
 * @see     <http://goo.gl/u4UUC>
 *
 * @param   {String}  string   The Unicode input string (UCS-2).
 *
 * @return  {Array}   The new array of code points.
 */
function ucs2decode(string) {
    const output = [];
    let counter = 0;
    const length = string.length;
    while (counter < length) {
        const value = string.charCodeAt(counter++);
        if (value >= 0xD800 && value <= 0xDBFF && counter < length) {
            // It's a high surrogate, and there is a next character.
            const extra = string.charCodeAt(counter++);
            if ((extra & 0xFC00) == 0xDC00) { // Low surrogate.
                output.push(((value & 0x3FF) << 10) + (extra & 0x3FF) + 0x10000);
            } else {
                // It's an unmatched surrogate; only append this code unit, in case the
                // next code unit is the high surrogate of a surrogate pair.
                output.push(value);
                counter--;
            }
        } else {
            output.push(value);
        }
    }
    return output;
}

export default {

    mixins: [Fieldtype],

    components: {
        Selector,
        Uploader,
        Uploads,
    },

    data: function() {
        return {
            data: this.value || '',
            buttons: [],
            mode: 'write',
            selections: null,
            showAssetSelector: false,
            selectedAssets: [],
            selectorViewMode: null,
            draggingFile: false,
            showCheatsheet: false,
            fullScreenMode: false,
            darkMode: false,
            codemirror: null,
            uploads: [],
            count: {
                characters: 0,
                words: 0,
            },
            escBinding: null,
            markdownPreviewText: null
        };
    },

    watch: {

        data(data) {
            this.updateDebounced(data);
            this.updateCount(data);
        },

        fullScreenMode: {
            immediate: true,
            handler: function (fullscreen) {
                this.$nextTick(() => {
                    this.$nextTick(() => this.initCodeMirror());
                });
            }
        },

        mode(mode) {
            if (mode === 'preview') this.updateMarkdownPreview();
        },

        readOnly(readOnly) {
            this.codemirror.setOption('readOnly', readOnly ? 'nocursor' : false);
        },

    },

    mounted() {
        this.initToolbarButtons();

        if (this.data) {
            this.updateCount(this.data);
        }

        let el = document.querySelector(`label[for="${this.fieldId}"]`);
        if (el) {
            el.addEventListener('click', () => {
                this.codemirror.focus();
            });
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

        toggleFullScreen() {
            if (this.fullScreenMode) {
                this.closeFullScreen();
            } else {
                this.openFullScreen();
            }
        },

        toggleDarkMode() {
            this.darkMode = ! this.darkMode;
        },

        getText: function(selection) {
            var i = _.indexOf(this.selections, selection);

            return this.codemirror.getSelections()[i];
        },

        toggleInline(type) {
            var self = this;
            var replacements = [];
            let elements = {
                "bold": {
                    "pattern": /^\*{2}(.*)\*{2}$/,
                    "delimiter": "**"
                },
                "code": {
                    "pattern": /^\`(.*)\`$/,
                    "delimiter": "`"
                },
                "italic": {
                    "pattern": /^\_(.*)\_$/,
                    "delimiter": "_"
                },
                "strikethrough": {
                    "pattern": /^\~\~(.*)\~\~$/,
                    "delimiter": "~~"
                }
            };

            _.each(self.selections, function (selection) {
                let delimiter = elements[type]['delimiter'];
                let replacement = (self.getText(selection).match(elements[type]['pattern']))
                    ? self.removeInline(selection, elements[type]['delimiter'])
                    : delimiter + self.getText(selection) + delimiter;

                replacements.push(replacement);
            });

            this.codemirror.replaceSelections(replacements, 'around');

            this.codemirror.focus();
        },

        toggleBlock(type) {
            var self = this;
            var replacements = [];
            let elements = {
                "code": {
                    "pattern": /^\`\`\`(.*)\n(.*)\n\`\`\`$/,
                    "delimiter": "\`\`\`"
                },
            };

            _.each(self.selections, function (selection) {
                let text = self.getText(selection);
                let delimiter = elements[type]['delimiter'];
                let replacement = (text.match(elements[type]['pattern']))
                    ? self.removeInline(selection, delimiter)
                    : delimiter + "\n" + text + "\n" + delimiter;

                replacements.push(replacement);
            });

            this.codemirror.replaceSelections(replacements, 'around');

            this.codemirror.focus();
        },

        removeInline: function (selection, delimiter) {
            var text = this.getText(selection);
            var blockLength = delimiter.length;

            return text.substring(blockLength, text.length-blockLength);
        },

        toggleLine(type) {
            let startPoint = this.codemirror.getCursor("start");
            let endPoint = this.codemirror.getCursor("end");
            let patterns = {
                "quote": /^(\s*)\>\s+/,
                "unordered-list": /^(\s*)(\*|\-|\+)\s+/,
                "ordered-list": /^(\s*)\d+\.\s+/
            };
            let map = {
                "quote": "> ",
                "unordered-list": "- ",
                "ordered-list": "1. "
            };

            for (let i = startPoint.line; i <= endPoint.line; i++) {
                let text = this.codemirror.getLine(i);
                text = this.isInside(type) ? text.replace(patterns[type], "$1") : map[type] + text;

                this.codemirror.replaceRange(text, { line: i, ch: 0 }, { line: i, ch: Infinity });
            }

            this.codemirror.focus();
        },

        // Get the state of the current position to see what elements it may be inside
        getState(position) {
            position = position || this.codemirror.getCursor("start");
            let state = this.codemirror.getTokenAt(position);

            if(!state.type) return {};

            let types = state.type.split(" ");

            let ret = {},
                data, text;

            for (var i = 0; i < types.length; i++) {
                data = types[i];

                if (data === "strong") {
                    ret.bold = true;
                } else if(data === "variable-2") {
                    text = this.codemirror.getLine(position.line);
                    if (/^\s*\d+\.\s/.test(text)) {
                        ret["ordered-list"] = true;
                    } else {
                        ret["unordered-list"] = true;
                    }
                } else if (data === "atom") {
                    ret.quote = true;
                } else if (data === "em") {
                    ret.italic = true;
                } else if (data === "quote") {
                    ret.quote = true;
                } else if (data === "strikethrough") {
                    ret.strikethrough = true;
                } else if (data === "comment") {
                    ret.code = true;
                } else if (data === "link") {
                    ret.link = true;
                } else if (data === "tag") {
                    ret.image = true;
                } else if (data.match(/^header(\-[1-6])?$/)) {
                    ret[data.replace("header", "heading")] = true;
                }
            }

            return ret;
        },

        // Check if position is inside a specific element
        isInside(type) {
            return this.getState()[type] ?? false;
        },

        insertTable() {
            let doc = this.codemirror.getDoc();
            let cursor = doc.getCursor();
            let line = doc.getLine(cursor.line);
            let pos = { line: cursor.line };
            let table = "|     |     |\n| --- | --- |\n|     |     |";

            if (line.length === 0) {
                doc.replaceRange(table, pos);
                this.codemirror.focus();
                this.codemirror.setCursor(cursor.line, 2);
            } else {
                doc.replaceRange("\n\n" + table, pos);
                this.codemirror.focus();
                this.codemirror.setCursor(cursor.line + 2, 2);
            }
        },

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
                    return;
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

        appendLink: function(url, text) {
            text = text || '';
            this.data += '\n\n['+text+']('+url+')';
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
            var mod = e.metaKey === true || e.ctrlKey === true;

            if (mod && key === 66) { // cmd+b
                this.toggleInline('bold');
                e.preventDefault();
            }

            if (mod && key === 73) { // cmd+i
                this.toggleInline('italic');
                e.preventDefault();
            }

            if (mod && key === 190) { // cmd+.
                this.toggleLine('quote');
                e.preventDefault();
            }

            if (mod && key === 192) { // ctrl+` (tick)
                this.toggleInline('code');
                e.preventDefault();
            }

            if (mod && key === 76) { // cmd+l
                this.toggleLine('unordered-list');
                e.preventDefault();
            }

            if (mod && key === 79) { // cmd+o
                this.toggleLine('ordered-list');
                e.preventDefault();
            }

            if (mod && key === 220) { // cmd+\
                this.toggleBlock('code');
                e.preventDefault();
            }

            if (mod && key === 75) { // cmd+k
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

            this.$axios.post(cp_url('assets-fieldtype'), { assets }).then(response => {
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
        },

        updateMarkdownPreview() {
            this.$axios
                .post(this.meta.previewUrl, { value: this.data, config: this.config })
                .then(response => this.markdownPreviewText = response.data)
                .catch(e => this.$toast.error(e.response ? e.response.data.message : __('Something went wrong')));
        },

        initCodeMirror() {
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
        },

        refresh() {
            this.$nextTick(function() {
                this.codemirror.refresh();
            })
        },

        initToolbarButtons() {
            let buttons = this.config.buttons.map(button => {
                return _.findWhere(availableButtons(), { name: button.toLowerCase() }) || button;
            });

            // Remove buttons that don't pass conditions.
            // eg. only the insert asset button can be shown if a container has been set.
            buttons = buttons.filter(button => {
                return (button.condition) ? button.condition.call(null, this.config) : true;
            });

            this.buttons = buttons;
        },

        updateCount(data) {
            let trimmed = data.trim();

            this.count.characters = ucs2decode(trimmed.replace(/\s/g, '')).length;
            this.count.words = trimmed.split(/\s+/).filter(word => word.length > 0).length;
        },

        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
        },
    },

    computed: {
        assetsEnabled: function() {
            return Boolean(this.config && this.config.container);
        },

        container: function() {
            return this.config.container;
        },

        editor() {
            return this;
        },

        folder: function() {
            return this.config.folder || '/';
        },

        restrictAssetNavigation() {
            return this.config.restrict_assets || false;
        },

        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

            return marked(this.data || '', { renderer: new PlainTextRenderer })
                .replace(/<\/?[^>]+(>|$)/g, "");
        },

        internalFieldActions() {
            return [
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => vm.fullScreenMode ? 'shrink-all' : 'expand-bold',
                    quick: true,
                    visibleWhenReadOnly: true,
                    run: this.toggleFullscreen,
                },
            ];
        },
    }

};
</script>
