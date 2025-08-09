<template>
    <portal name="markdown-fullscreen" :disabled="!fullScreenMode" target-class="markdown-fieldtype">
        <element-container @resized="refresh">
            <div
                class="
                    @container/markdown w-full block bg-white dark:bg-gray-900 rounded-lg relative
                    border border-gray-300 with-contrast:border-gray-500 dark:border-x-0 dark:border-t-0 dark:border-white/10 dark:inset-shadow-2xs dark:inset-shadow-black
                    text-gray-900 dark:text-gray-300
                    appearance-none antialiased shadow-ui-sm disabled:shadow-none
                "
                :class="{
                    'markdown-fullscreen': fullScreenMode,
                    'markdown-dark-mode': darkMode,
                    'border-dashed': isReadOnly,
                }"
            >
                <uploader
                    ref="uploader"
                    :enabled="assetsEnabled"
                    :container="container"
                    :path="folder"
                    @updated="uploadsUpdated"
                    @upload-complete="uploadComplete"
                    v-slot="{ dragging }"
                >
                    <div>
                        <publish-field-fullscreen-header
                            v-if="fullScreenMode"
                            :title="config.display"
                            :field-actions="fieldActions"
                            @close="toggleFullscreen"
                        >
                            <markdown-toolbar
                                v-if="fullScreenMode"
                                v-model:mode="mode"
                                :buttons="buttons"
                                :is-read-only="isReadOnly"
                                :show-dark-mode="fullScreenMode"
                                :dark-mode="darkMode"
                                :is-fullscreen="true"
                                @toggle-dark-mode="toggleDarkMode"
                                @button-click="handleButtonClick"
                            />
                        </publish-field-fullscreen-header>

                        <markdown-toolbar
                            v-if="!fullScreenMode && showFixedToolbar"
                            v-model:mode="mode"
                            :buttons="buttons"
                            :is-read-only="isReadOnly"
                            :show-dark-mode="false"
                            :dark-mode="darkMode"
                            :is-fullscreen="false"
                            @toggle-dark-mode="toggleDarkMode"
                            @button-click="handleButtonClick"
                        />

                        <div class="drag-notification" v-show="dragging">
                            <svg-icon name="upload" class="mb-4 size-12" />
                            {{ __('Drop File to Upload') }}
                        </div>

                        <uploads v-if="uploads.length" :uploads="uploads" class="-mt-px" />

                        <div :class="`mode-wrap mode-${mode}`, { 'prose p-3': mode == 'preview' }" @click="focus">
                            <div
                                class="markdown-writer"
                                ref="writer"
                                v-show="mode == 'write'"
                                @dragover="draggingFile = true"
                                @dragleave="draggingFile = false"
                                @drop="draggingFile = false"
                                @keydown="shortcut"
                            >
                                <div class="editor relative z-6 st-text-legibility focus-within:focus-outline focus-outline-discrete" ref="codemirror">
                                    <div
                                        v-if="showFloatingToolbar && toolbarIsFloating && !isReadOnly"
                                        class="markdown-floating-toolbar absolute z-50 flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2 py-1 shadow-lg dark:border-white/10 dark:bg-gray-900"
                                        :style="{ left: `${floatingToolbarX}px`, top: `${floatingToolbarY}px` }"
                                        @mousedown.prevent
                                    >
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            class="px-2! [&_svg]:size-3.5"
                                            v-for="button in buttons"
                                            :key="button.name"
                                            v-tooltip="button.text"
                                            :aria-label="button.text"
                                            @click="handleButtonClick(button.command)"
                                        >
                                            <svg-icon :name="button.svg" class="size-4" />
                                        </Button>
                                    </div>
                                </div>
                                <!-- Hidden input for label association -->
                                <input
                                    v-if="id"
                                    :id="id"
                                    type="text"
                                    class="sr-only"
                                    @focus="focusCodeMirror"
                                    tabindex="-1"
                                />

                                    <footer class="flex items-center justify-between bg-gray-50 dark:bg-gray-950 rounded-b-lg border-t border-gray-200 dark:border-white/10 p-1 text-sm w-full" :class="{ 'absolute inset-x-0 bottom-0': fullScreenMode }">
                                        <div class="markdown-cheatsheet-helper">
                                            <Button
                                                icon="markdown"
                                                size="sm"
                                                variant="subtle"
                                                @click="showCheatsheet = true"
                                                :aria-label="__('Show Markdown Cheatsheet')"
                                                :text="__('Markdown Cheatsheet')"
                                            />
                                        </div>
                                        <div v-if="fullScreenMode" class="flex items-center pe-2 gap-3 text-xs">
                                            <div class="whitespace-nowrap">
                                                <span v-text="count.words" /> {{ __('Words') }}
                                            </div>
                                            <div class="whitespace-nowrap">
                                                <span v-text="count.characters" /> {{ __('Characters') }}
                                            </div>
                                        </div>
                                    </footer>

                                <div class="drag-notification" v-if="assetsEnabled && draggingFile">
                                    <svg-icon name="upload" class="mb-4 size-12" />
                                    {{ __('Drop File to Upload') }}
                                </div>
                            </div>

                            <div
                                v-show="mode == 'preview'"
                                v-html="markdownPreviewText"
                                class="markdown-preview p-3 prose prose-sm @md/markdown:prose-base"
                            ></div>
                        </div>
                    </div>
                </uploader>

                <stack
                    v-if="showAssetSelector && !isReadOnly"
                    name="markdown-asset-selector"
                    @closed="closeAssetSelector"
                >
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
                    <div class="relative h-full overflow-auto bg-white p-6 dark:bg-dark-600">
                        <Button
                            icon="x"
                            variant="ghost"
                            class="absolute top-0 mt-4 ltr:right-0 ltr:mr-8 rtl:left-0 rtl:ml-8"
                            @click="close"
                        />
                        <div class="prose mx-auto my-8 max-w-md">
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
import Fieldtype from '../Fieldtype.vue';
import { marked } from 'marked';
import { markRaw } from 'vue';
import { TextRenderer as PlainTextRenderer } from '@davidenke/marked-text-renderer';
import throttle from '@statamic/util/throttle.js';
import { Button } from '@statamic/ui';

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
import MarkdownToolbar from './MarkdownToolbar.vue';
// Keymaps
import 'codemirror/keymap/sublime';

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
        if (value >= 0xd800 && value <= 0xdbff && counter < length) {
            // It's a high surrogate, and there is a next character.
            const extra = string.charCodeAt(counter++);
            if ((extra & 0xfc00) == 0xdc00) {
                // Low surrogate.
                output.push(((value & 0x3ff) << 10) + (extra & 0x3ff) + 0x10000);
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
        Button,
        Selector,
        Uploader,
        Uploads,
        MarkdownToolbar,
    },

    data() {
        return {
            data: this.value || '',
            buttons: [],
            mode: 'write',
            selections: null,
            showAssetSelector: false,
            selectedAssets: [],
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
            markdownPreviewText: null,
            showFloatingToolbar: false,
            floatingToolbarX: 0,
            floatingToolbarY: 0,
        };
    },

    watch: {
        data: {
            handler(data) {
                this.updateDebounced(data);
                this.updateCount(data);
            },
        },
        mode: {
            handler(mode) {
                if (mode === 'preview') this.updateMarkdownPreview();
            },
        },
        readOnly: {
            handler(readOnly) {
                this.codemirror.setOption('readOnly', readOnly ? 'nocursor' : false);
            },
        },
    },

    mounted() {
        this.initToolbarButtons();
        this.$nextTick(() => {
            this.initCodeMirror();
        });

        if (this.data) {
            this.updateCount(this.data);
        }
    },

    beforeUnmount() {
        this.$events.$off('livepreview.opened', this.throttledResizeEvent);
        this.$events.$off('livepreview.closed', this.throttledResizeEvent);
        this.$events.$off('livepreview.resizing', this.throttledResizeEvent);

        // Clean up CodeMirror event listeners
        if (this.codemirror && this.toolbarIsFloating) {
            this.codemirror.off('cursorActivity', this.handleCursorActivity);
            this.codemirror.off('blur', this.hideFloatingToolbar);
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

        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
            this.trackHeightUpdates();
        },

        toggleDarkMode() {
            this.darkMode = !this.darkMode;
        },

        getText(selection) {
            const i = this.selections.indexOf(selection);

            return this.codemirror.getSelections()[i];
        },

        toggleInline(type) {
            const elements = {
                bold: { pattern: /^\*{2}(.*)\*{2}$/, delimiter: '**' },
                code: { pattern: /^\`(.*)\`$/, delimiter: '`' },
                italic: { pattern: /^\_(.*)\_$/, delimiter: '_' },
                strikethrough: { pattern: /^\~\~(.*)\~\~$/, delimiter: '~~' },
            };

            const replacements = this.selections.map(selection => {
                const text = this.getText(selection);
                const { delimiter, pattern } = elements[type];
                return text.match(pattern)
                    ? this.removeInline(selection, delimiter)
                    : `${delimiter}${text}${delimiter}`;
            });

            this.codemirror.replaceSelections(replacements, 'around');
            this.codemirror.focus();
        },

        toggleBlock(type) {
            const replacements = this.selections.map(selection => {
                const text = this.getText(selection);
                const delimiter = '```';
                return text.match(new RegExp(`^\`\`\`(.*)\n(.*)\n\`\`\`$`))
                    ? this.removeInline(selection, delimiter)
                    : `${delimiter}\n${text}\n${delimiter}`;
            });

            this.codemirror.replaceSelections(replacements, 'around');
            this.codemirror.focus();
        },

        removeInline(selection, delimiter) {
            const text = this.getText(selection);
            return text.slice(delimiter.length, -delimiter.length);
        },

        toggleLine(type) {
            const startPoint = this.codemirror.getCursor('start');
            const endPoint = this.codemirror.getCursor('end');
            const patterns = {
                quote: /^(\s*)\>\s+/,
                'unordered-list': /^(\s*)(\*|\-|\+)\s+/,
                'ordered-list': /^(\s*)\d+\.\s+/,
            };
            const prefixes = {
                quote: '> ',
                'unordered-list': '- ',
                'ordered-list': '1. ',
            };

            for (let i = startPoint.line; i <= endPoint.line; i++) {
                const text = this.codemirror.getLine(i);
                const newText = this.isInside(type)
                    ? text.replace(patterns[type], '$1')
                    : prefixes[type] + text;

                this.codemirror.replaceRange(newText, { line: i, ch: 0 }, { line: i, ch: Infinity });
            }

            this.codemirror.focus();
        },

        getState(position) {
            position = position || this.codemirror.getCursor('start');
            const state = this.codemirror.getTokenAt(position);
            if (!state.type) return {};

            const types = state.type.split(' ');
            const ret = {};
            const text = this.codemirror.getLine(position.line);

            types.forEach(type => {
                switch (type) {
                    case 'strong':
                        ret.bold = true;
                        break;
                    case 'variable-2':
                        ret[/^\s*\d+\.\s/.test(text) ? 'ordered-list' : 'unordered-list'] = true;
                        break;
                    case 'atom':
                    case 'quote':
                        ret.quote = true;
                        break;
                    case 'em':
                        ret.italic = true;
                        break;
                    case 'strikethrough':
                        ret.strikethrough = true;
                        break;
                    case 'comment':
                        ret.code = true;
                        break;
                    case 'link':
                        ret.link = true;
                        break;
                    case 'tag':
                        ret.image = true;
                        break;
                    default:
                        if (type.match(/^header(\-[1-6])?$/)) {
                            ret[type.replace('header', 'heading')] = true;
                        }
                }
            });

            return ret;
        },

        isInside(type) {
            return this.getState()[type] ?? false;
        },

        insertTable() {
            const doc = this.codemirror.getDoc();
            const cursor = doc.getCursor();
            const line = doc.getLine(cursor.line);
            const pos = { line: cursor.line };
            const table = '|     |     |\n| --- | --- |\n|     |     |';

            if (line.length === 0) {
                doc.replaceRange(table, pos);
            } else {
                doc.replaceRange('\n\n' + table, pos);
                cursor.line += 2;
            }

            this.codemirror.focus();
            this.codemirror.setCursor(cursor.line, 2);
        },

        insertImage(url, alt) {
            const doc = this.codemirror.doc;
            const selection = doc.somethingSelected() ? doc.getSelection() : alt || '';

            const imageText = `![${selection}](${url || ''})`;
            doc.replaceSelection(imageText, 'start');

            // Select the text
            const line = doc.getCursor().line;
            const start = doc.getCursor().ch + 2; // move past the ![
            const end = start + selection.length;
            doc.setSelection({ line, ch: start }, { line, ch: end });

            this.codemirror.focus();
        },

        appendImage(url, alt = '') {
            this.data += `\n\n![${alt}](${url})`;
        },

        insertLink(url, text) {
            const doc = this.codemirror.doc;
            const selection = doc.somethingSelected() ? doc.getSelection() : text || '';

            if (!url) {
                url = prompt(__('Enter URL'), 'https://');
                if (!url) return;
            }

            const linkText = `[${selection}](${url})`;
            doc.replaceSelection(linkText, 'start');

            // Select the text
            const line = doc.getCursor().line;
            const start = doc.getCursor().ch + 1; // move past the first [
            const end = start + selection.length;
            doc.setSelection({ line, ch: start }, { line, ch: end });

            this.codemirror.focus();
        },

        appendLink(url, text = '') {
            this.data += `\n\n[${text}](${url})`;
        },

        /**
         * Open the asset selector
         */
        addAsset () {
            if (!this.assetsEnabled) return;
            this.showAssetSelector = true;
        },

        /**
         * Execute a keyboard shortcut, when applicable
         */
        shortcut(e) {
            const mod = e.metaKey || e.ctrlKey;
            if (!mod) return;

            // Handle Cmd+Shift+A for asset insertion
            if (this.assetsEnabled && e.shiftKey && e.keyCode === 65) {
                e.preventDefault();
                this.addAsset();
                return;
            }

            const shortcuts = {
                66: () => this.toggleInline('bold'), // cmd+b
                73: () => this.toggleInline('italic'), // cmd+i

                // TODO: Deprecate these hotkeys?
                // 190: () => this.toggleLine('quote'), // cmd+.
                // 192: () => this.toggleInline('code'), // cmd+`
                // 76: () => this.toggleLine('unordered-list'), // cmd+l <-- This conflicts with most browsers re: cmd+l for location
                // 79: () => this.toggleLine('ordered-list'), // cmd+o
                // 220: () => this.toggleBlock('code'), // cmd+\
                // 75: () => this.insertLink(), // cmd+k <-- This conflicts with Command Palette
            };

            if (shortcuts[e.keyCode]) {
                e.preventDefault();
                shortcuts[e.keyCode]();
            }
        },

        /**
         * When assets are selected from the modal, this event gets fired.
         *
         * @param  Array assets  All the assets that were selected
         */
        assetsSelected(assets) {
            this.closeAssetSelector();
            this.selectedAssets = [];

            this.$axios.post(cp_url('assets-fieldtype'), { assets }).then(({ data }) => {
                data.forEach(asset => {
                    const alt = asset.values.alt || '';
                    const url = encodeURI(`statamic://${asset.reference}`);
                    const method = assets.length === 1 ? 'insert' : 'append';

                    if (asset.isImage) {
                        this[`${method}Image`](url, alt);
                    } else {
                        this[`${method}Link`](url, alt);
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

        focusCodeMirror() {
            if (this.codemirror) {
                this.codemirror.focus();
            }
        },



        trackHeightUpdates() {
            this.$events.$on('livepreview.opened', this.throttledResizeEvent);
            this.$events.$on('livepreview.closed', this.throttledResizeEvent);
            this.$events.$on('livepreview.resizing', this.throttledResizeEvent);
        },

        throttledResizeEvent: throttle(function () {
            window.dispatchEvent(new Event('resize'));
        }, 100),

        updateMarkdownPreview() {
            this.$axios
                .post(this.meta.previewUrl, { value: this.data, config: this.config })
                .then((response) => (this.markdownPreviewText = response.data))
                .catch((e) => this.$toast.error(e.response ? e.response.data.message : __('Something went wrong')));
        },

        initCodeMirror() {
            var self = this;

            self.codemirror = markRaw(
                CodeMirror(this.$refs.codemirror, {
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
                        Enter: 'newlineAndIndentContinueMarkdownList',
                        'Cmd-Left': 'goLineLeftSmart',
                    },
                }),
            );

                        // Set up floating toolbar event listeners if in floating mode
            if (this.toolbarIsFloating) {
                self.codemirror.on('cursorActivity', this.handleCursorActivity);
                self.codemirror.on('blur', this.hideFloatingToolbar);
            }

            // Note: ID is set on a hidden input element for label association
            // The CodeMirror element doesn't need the ID attribute

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
            this.$watch('value', function (val) {
                if (val !== self.codemirror.doc.getValue()) {
                    self.codemirror.doc.setValue(val);
                }
            });

            this.trackHeightUpdates();
        },

        refresh() {
            this.$nextTick(function () {
                this.codemirror.refresh();
            });
        },

        initToolbarButtons() {
            let buttons = this.config.buttons.map((button) => {
                return availableButtons().find((b) => b.name === button.toLowerCase()) || button;
            });

            // Remove buttons that don't pass conditions.
            // eg. only the insert asset button can be shown if a container has been set.
            buttons = buttons.filter((button) => {
                return button.condition ? button.condition.call(null, this.config) : true;
            });

            this.buttons = buttons;
        },

        updateCount(data) {
            const trimmed = data.trim();
            const characters = ucs2decode(trimmed.replace(/\s/g, '')).length;
            const words = trimmed.split(/\s+/).filter(word => word.length > 0).length;

            this.count = { characters, words };
        },

        handleButtonClick(command) {
            command(this);
        },

        handleCursorActivity() {
            if (!this.toolbarIsFloating) return;

            const selection = this.codemirror.getSelection();

            if (selection && selection.length > 0 && !this.isReadOnly) {
                const doc = this.codemirror.getDoc();
                this.selections = doc.listSelections();

                this.showFloatingToolbar = true;
                this.updateFloatingToolbarPosition();
            } else {
                this.showFloatingToolbar = false;
            }
        },

        hideFloatingToolbar() {
            this.showFloatingToolbar = false;
        },

        updateFloatingToolbarPosition() {
            if (!this.codemirror || !this.showFloatingToolbar) return;

            const from = this.codemirror.getCursor('from');
            const to = this.codemirror.getCursor('to');

            const fromCoords = this.codemirror.cursorCoords(from);
            const toCoords = this.codemirror.cursorCoords(to);

            const editorRect = this.codemirror.getWrapperElement().getBoundingClientRect();
            const x = Math.round((fromCoords.left + toCoords.right) / 2 - editorRect.left);
            const y = Math.round(fromCoords.top - editorRect.top - 50);

            this.floatingToolbarX = x;
            this.floatingToolbarY = y;
        },
    },

    computed: {
        assetsEnabled() {
            return Boolean(this.config?.container);
        },

        container() {
            return this.config.container;
        },

        editor() {
            return this;
        },

        folder() {
            return this.config.folder || '/';
        },

        restrictAssetNavigation() {
            return this.config.restrict_assets || false;
        },

        toolbarIsFixed() {
            return this.config.toolbar_mode === 'fixed';
        },

        toolbarIsFloating() {
            return this.config.toolbar_mode === 'floating';
        },

        showFixedToolbar() {
            return this.toolbarIsFixed && this.buttons.length > 0;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return marked(this.data || '', { renderer: new PlainTextRenderer() }).replace(/<\/?[^>]+(>|$)/g, '');
        },

        internalFieldActions() {
            return [
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => (vm.fullScreenMode ? 'shrink-all' : 'expand-bold'),
                    quick: true,
                    visibleWhenReadOnly: true,
                    run: this.toggleFullscreen,
                },
            ];
        },
    },
};
</script>
