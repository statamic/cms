<template>

<portal name="bard-fullscreen" :disabled="!fullScreenMode" :provide="provide">
<!-- These wrappers allow any css that expected the field to
     be within the context of a publish form to continue working
     once it has been portaled out. -->
<div :class="{ 'publish-fields': fullScreenMode }">
<div :class="fullScreenMode && wrapperClasses">

    <div
        class="bard-fieldtype-wrapper"
        :class="{'bard-fullscreen': fullScreenMode }"
        ref="container"
        @dragstart.stop="ignorePageHeader(true)"
        @dragend="ignorePageHeader(false)"
    >

        <publish-field-fullscreen-header
            v-if="fullScreenMode"
            :config="config"
            :field-actions="visibleFieldActions"
            @close="toggleFullscreen">
            <div class="bard-fixed-toolbar border-0" v-if="!readOnly && showFixedToolbar">
                <div class="flex flex-wrap flex-1 items-center no-select" v-if="toolbarIsFixed">
                    <component
                        v-for="button in visibleButtons(buttons)"
                        :key="button.name"
                        :is="button.component || 'BardToolbarButton'"
                        :button="button"
                        :active="buttonIsActive(button)"
                        :config="config"
                        :bard="_self"
                        :editor="editor" />
                    <button class="bard-toolbar-button" @click="showSource = !showSource" v-if="allowSource" v-tooltip="__('Show HTML Source')" :aria-label="__('Show HTML Source')">
                        <svg-icon name="show-source" class="w-4 h-4 "/>
                    </button>
                </div>
            </div>
        </publish-field-fullscreen-header>

        <div class="bard-fixed-toolbar" v-if="!readOnly && showFixedToolbar && !fullScreenMode">
            <div class="flex flex-wrap flex-1 items-center no-select" v-if="toolbarIsFixed">
                <component
                    v-for="button in visibleButtons(buttons)"
                    :key="button.name"
                    :is="button.component || 'BardToolbarButton'"
                    :button="button"
                    :active="buttonIsActive(button)"
                    :config="config"
                    :bard="_self"
                    :editor="editor" />
                <button class="bard-toolbar-button" @click="showSource = !showSource" v-if="allowSource" v-tooltip="__('Show HTML Source')" :aria-label="__('Show HTML Source')">
                    <svg-icon name="show-source" class="w-4 h-4 "/>
                </button>
            </div>
        </div>

        <div class="bard-editor @container/bard" :class="{ 'mode:read-only': readOnly, 'mode:minimal': ! showFixedToolbar, 'mode:inline': inputIsInline }" tabindex="0">
            <bubble-menu class="bard-floating-toolbar" :editor="editor" :tippy-options="{ maxWidth: 'none', zIndex: 1000 }" v-if="editor && toolbarIsFloating && !readOnly">
                <component
                    v-for="button in visibleButtons(buttons)"
                    :key="button.name"
                    :is="button.component || 'BardToolbarButton'"
                    :button="button"
                    :active="buttonIsActive(button)"
                    :bard="_self"
                    :config="config"
                    :editor="editor" />
            </bubble-menu>

            <floating-menu
                class="bard-set-selector"
                :editor="editor"
                :should-show="shouldShowSetButton"
                :is-showing="showAddSetButton"
                v-if="editor"
                v-slot="{ y }"
                @shown="showAddSetButton = true"
                @hidden="showAddSetButton = false"
            >
                <set-picker
                    v-if="showAddSetButton"
                    :sets="groupConfigs"
                    @added="addSet"
                    @clicked-away="clickedAwayFromSetPicker"
                >
                    <template #trigger>
                        <button
                            type="button"
                            class="btn-round group bard-add-set-button"
                            :style="{ transform: `translateY(${y}px)` }"
                            :aria-label="__('Add Set')"
                            v-tooltip="__('Add Set')"
                            @click="addSetButtonClicked"
                        >
                            <svg-icon name="micro/plus" class="w-3 h-3 text-gray-800 dark:text-dark-175 group-hover:text-black dark:group-hover:dark-text-100" />
                        </button>
                    </template>
                </set-picker>
            </floating-menu>

            <div class="bard-error" v-if="initError" v-html="initError"></div>
            <editor-content :editor="editor" v-show="!showSource" :id="fieldId" />
            <bard-source :html="htmlWithReplacedLinks" v-if="showSource" />
        </div>
        <div class="bard-footer-toolbar" v-if="editor && (config.reading_time || config.character_limit || config.word_count)">
            <div v-if="config.reading_time">{{ readingTime }} {{ __('Reading Time') }}</div>
            <div v-else />
            <div v-if="config.character_limit || config.word_count" v-text="characterAndWordCountText" />
        </div>
    </div>
</div>
</div>
</portal>

</template>

<script>
import uniqid from 'uniqid';
import reduce from 'underscore/modules/reduce';
import { BubbleMenu, Editor, EditorContent } from '@tiptap/vue-2';
import { Extension } from '@tiptap/core';
import { FloatingMenu } from './FloatingMenu';
import Blockquote from '@tiptap/extension-blockquote';
import Bold from '@tiptap/extension-bold';
import BulletList from '@tiptap/extension-bullet-list';
import CharacterCount from '@tiptap/extension-character-count';
import Code from '@tiptap/extension-code';
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import Dropcursor from '@tiptap/extension-dropcursor';
import Gapcursor from '@tiptap/extension-gapcursor';
import HardBreak from '@tiptap/extension-hard-break';
import Heading from '@tiptap/extension-heading';
import History from '@tiptap/extension-history';
import HorizontalRule from '@tiptap/extension-horizontal-rule';
import Italic from '@tiptap/extension-italic';
import ListItem from '@tiptap/extension-list-item';
import OrderedList from '@tiptap/extension-ordered-list';
import Paragraph from '@tiptap/extension-paragraph';
import Placeholder from '@tiptap/extension-placeholder';
import Strike from '@tiptap/extension-strike';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import Table from '@tiptap/extension-table';
import TableRow from '@tiptap/extension-table-row';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import Text from '@tiptap/extension-text';
import TextAlign from '@tiptap/extension-text-align';
import Typography from '@tiptap/extension-typography';
import Underline from '@tiptap/extension-underline';
import BardSource from './Source.vue';
import SetPicker from '../replicator/SetPicker.vue';
import { DocumentBlock, DocumentInline } from './Document';
import { Set } from './Set'
import { Small } from './Small';
import { Image } from './Image';
import { Link } from './Link';
import LinkToolbarButton from './LinkToolbarButton.vue';
import ManagesSetMeta from '../replicator/ManagesSetMeta';
import { availableButtons, addButtonHtml } from '../bard/buttons';
import readTimeEstimate from 'read-time-estimate';
import { lowlight } from 'lowlight/lib/common.js';
import 'highlight.js/styles/github.css';

export default {

    mixins: [Fieldtype, ManagesSetMeta],

    components: {
        BubbleMenu,
        BardSource,
        BardToolbarButton,
        SetPicker,
        EditorContent,
        FloatingMenu,
        LinkToolbarButton,
    },

    inject: ['storeName'],

    data() {
        return {
            editor: null,
            html: null,
            json: [],
            showSource: false,
            fullScreenMode: false,
            buttons: [],
            collapsed: this.meta.collapsed,
            previews: this.meta.previews,
            mounted: false,
            initError: null,
            pageHeader: null,
            escBinding: null,
            showAddSetButton: false,
            provide: {
                bard: this.makeBardProvide(),
                storeName: this.storeName,
                bardSets: this.config.sets
            }
        }
    },

    computed: {

        allowSource() {
            return this.config.allow_source === undefined ? true : this.config.allow_source;
        },

        toolbarIsFixed() {
            return this.config.toolbar_mode === 'fixed';
        },

        toolbarIsFloating() {
            return this.config.toolbar_mode === 'floating';
        },

        showFixedToolbar() {
            return this.toolbarIsFixed && (this.visibleButtons.length > 0 || this.allowSource || this.hasExtraButtons)
        },

        hasExtraButtons() {
            return this.allowSource || this.setConfigs.length > 0 || this.config.fullscreen;
        },

        readingTime() {
            if (this.html) {
                var stats = readTimeEstimate(this.html, 265, 12, 500, ['img', 'Image', 'bard-set']);
                var duration = moment.duration(stats.duration, 'minutes');

                return moment.utc(duration.asMilliseconds()).format("mm:ss");
            }
        },

        characterAndWordCountText() {
            const showWordCount = this.config.word_count;
            const wordCount = this.editor.storage.characterCount.words();
            const wordCountText = `${__n(':count word|:count words', wordCount)}`;
            const charLimit = this.config.character_limit;
            const showCharLimit = charLimit > 0;
            const charCount = this.editor.storage.characterCount.characters();

            // If both are enabled, show a more verbose combined string.
            if (showCharLimit && showWordCount) {
                return `${wordCountText}, ${__(':count/:total characters', { count: charCount, total: charLimit })}`;
            }

            // Otherwise show one or the other.
            if (showCharLimit) return `${charCount}/${charLimit}`;
            if (showWordCount) return wordCountText;
        },

        isFirstCreation() {
            return !this.$config.get('bard.meta').hasOwnProperty(this.id);
        },

        id() {
            return `${this.storeName}.${this.name}`;
        },

        setIndexes() {
            let indexes = {}

            this.json.forEach((item, i) => {
                if (item.type === 'set') {
                    indexes[item.attrs.id] = i;
                }
            });

            return indexes;
        },

        storeState() {
            if (! this.storeName) return undefined;

            return this.$store.state.publish[this.storeName];
        },

        site() {
            return this.storeState ? this.storeState.site : this.$config.get('selectedSite');
        },

        htmlWithReplacedLinks() {
            return this.html.replaceAll(/\"statamic:\/\/(.*?)\"/g, (match, ref) => {
                const linkData = this.meta.linkData[ref];
                if (! linkData) {
                    this.$toast.error(`${__('No link data found for')} ${ref}`);
                    return '""';
                }

                return `"${linkData.permalink}"`;
            });
        },

        setsWithErrors() {
            if (! this.storeState) return [];

            return Object.values(this.setIndexes).filter((setIndex) => {
                const prefix = `${this.fieldPathPrefix || this.handle}.${setIndex}.`;

                return Object.keys(this.storeState.errors).some(key => key.startsWith(prefix));
            })
        },

        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;
            const stack = [...this.value];
            let text = '';
            while (stack.length) {
                const node = stack.shift();
                if (node.type === 'text') {
                    text += ` ${node.text || ''}`;
                } else if (node.type === 'set') {
                    const handle = node.attrs.values.type;
                    const set = this.setConfigs.find(set => set.handle === handle);
                    text += ` [${__(set ? set.display : handle)}]`;
                }
                if (text.length > 150) {
                    break;
                }
                if (node.content) {
                    stack.unshift(...node.content);
                }
            }
            return text;
        },

        inputIsInline() {
            return this.config.inline;
        },

        wrapperClasses() {
            return `form-group publish-field publish-field__${this.handle} bard-fieldtype`;
        },

        setConfigs() {
            return reduce(this.groupConfigs, (sets, group) => {
                return sets.concat(group.sets);
            }, []);
        },

        groupConfigs() {
            return this.config.sets;
        },

        internalFieldActions() {
            return [
                {
                    title: __('Expand All Sets'),
                    icon: 'arrows-horizontal-expand',
                    quick: true,
                    run: this.expandAll,
                },
                {
                    title: __('Collapse All Sets'),
                    icon: 'arrows-horizontal-collapse',
                    quick: true,
                    run: this.collapseAll,
                },
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => vm.fullScreenMode ? 'shrink-all' : 'expand-bold',
                    quick: true,
                    run: this.toggleFullscreen,
                    visible: this.config.fullscreen,
                },
            ];
        },

    },

    mounted() {
        this.initToolbarButtons();
        this.initEditor();

        this.json = this.editor.getJSON().content;
        this.html = this.editor.getHTML();

        this.escBinding = this.$keys.bind('esc', this.closeFullscreen)

        this.$nextTick(() => {
            this.mounted = true;
            if (this.config.collapse) this.collapseAll();
        });

        this.pageHeader = document.querySelector('.global-header');

        this.$nextTick(() => {
            let el = document.querySelector(`label[for="${this.fieldId}"]`);
            if (el) {
                el.addEventListener('click', () => {
                    this.editor.commands.focus();
                });
            }
        });
    },

    beforeDestroy() {
        this.editor.destroy();
        this.escBinding.destroy();
    },

    watch: {

        json(json, oldJson) {
            if (!this.mounted) return;
                        
            if (json === oldJson) return;

            this.updateDebounced(json);
        },

        value(value, oldValue) {    
            const oldContent = this.editor.getJSON();
            const content = this.valueToContent(value);

            if (JSON.stringify(content) !== JSON.stringify(oldContent)) {
                this.editor.commands.clearContent()
                this.editor.commands.setContent(content, true);
            }
        },

        readOnly(readOnly) {
            this.editor.setEditable(!this.readOnly);
        },

        collapsed(value) {
            const meta = this.meta;
            meta.collapsed = value;
            this.updateMeta(meta);
        },

        previews: {
            deep: true,
            handler(value) {
                if (JSON.stringify(this.meta.previews) === JSON.stringify(value)) {
                    return
                }
                const meta = this.meta;
                meta.previews = value;
                this.updateMeta(meta);
            }
        },

        fullScreenMode() {
            this.initEditor();
        }

    },

    methods: {
        addSet(handle) {
            const id = uniqid();
            const values = Object.assign({}, { type: handle }, this.meta.defaults[handle]);

            let previews = {};
            Object.keys(this.meta.defaults[handle]).forEach(key => previews[key] = null);
            this.previews = Object.assign({}, this.previews, { [id]: previews });

            this.updateSetMeta(id, this.meta.new[handle]);

            const { $head } = this.editor.view.state.selection;
            const { nodeBefore } = $head;

            // Perform this in nextTick because the meta data won't be ready until then.
            this.$nextTick(() => {
                if (nodeBefore) {
                    this.editor.commands.setAt({ attrs: { id, values }, pos: $head.pos });
                } else {
                    this.editor.commands.set({ id, values });
                }
            });
        },

        duplicateSet(old_id, attrs, pos) {
            const id = uniqid();
            const enabled = attrs.enabled;
            const values = Object.assign({}, attrs.values);

            let previews = Object.assign({}, this.previews[old_id]);
            this.previews = Object.assign({}, this.previews, { [id]: previews });

            this.updateSetMeta(id, this.meta.existing[old_id]);

            // Perform this in nextTick because the meta data won't be ready until then.
            this.$nextTick(() => {
                this.editor.commands.setAt({ attrs: { id, enabled, values }, pos });
            });
        },

        pasteSet(attrs) {
            const old_id = attrs.id;
            const id = uniqid();
            const enabled = attrs.enabled;
            const values = Object.assign({}, attrs.values);

            let previews = Object.assign({}, this.previews[old_id] || {});
            this.previews = Object.assign({}, this.previews, { [id]: previews });

            this.updateSetMeta(id, this.meta.existing[old_id] || this.meta.defaults[values.type] || {});

            return { id, enabled, values };
        },

        collapseSet(id) {
            if (!this.collapsed.includes(id)) {
                this.collapsed.push(id)
            }
        },

        expandSet(id) {
            if (this.config.collapse === 'accordion') {
                this.collapsed = Object.keys(this.meta.existing).filter(v => v !== id);
                return;
            }

            if (this.collapsed.includes(id)) {
                var index = this.collapsed.indexOf(id);
                this.collapsed.splice(index, 1);
            }
        },

        collapseAll() {
            this.collapsed = Object.keys(this.meta.existing);
        },

        expandAll() {
            this.collapsed = [];
        },

        toggleCollapseSets() {
            (this.collapsed.length === 0) ? this.collapseAll() : this.expandAll();
        },

        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
        },

        closeFullscreen() {
            this.fullScreenMode = false;
        },

        shouldShowSetButton({ view, state }) {
            const { selection } = state;
            const { $anchor, empty } = selection;
            const isRootDepth = $anchor.depth === 1;
            const isEmptyTextBlock = $anchor.parent.isTextblock && !$anchor.parent.type.spec.code && !$anchor.parent.textContent;
            const isAroundInlineImage = state.selection.$to.nodeBefore?.type.name === 'image' || state.selection.$to.nodeAfter?.type.name === 'image'
            const isActive = view.hasFocus() && empty && isRootDepth && isEmptyTextBlock && !isAroundInlineImage;
            return this.setConfigs.length && (this.config.always_show_set_button || isActive);
        },

        initToolbarButtons() {
            const selectedButtons = this.config.buttons || [
                'h2', 'h3', 'bold', 'italic', 'unorderedlist', 'orderedlist', 'removeformat', 'quote', 'anchor', 'table'
            ];

            if (selectedButtons.includes('table')) {
                selectedButtons.push(
                    'deletetable',
                    'addcolumnbefore',
                    'addcolumnafter',
                    'deletecolumn',
                    'addrowbefore',
                    'addrowafter',
                    'deleterow',
                    'togglecellmerge',
                    'toggleheadercell'
                );
            }

            // Get the configured buttons and swap them with corresponding objects
            let buttons = selectedButtons.map(button => {
                return _.findWhere(availableButtons(), { name: button.toLowerCase() }) || button;
            });

            // Let addons add, remove, or control the position of buttons.
            this.$bard.buttonCallbacks.forEach(callback => {
                // Since the developer uses the same callback to add buttons to the field itself, and for the
                // button configurator, we need to make the button conditional when on the Bard fieldtype
                // but not in the button configurator. So here we'll filter it out if it's not selected.
                const buttonFn = (button) => selectedButtons.includes(button.name) ? button : null;

                const addedButtons = callback(buttons, buttonFn);

                // No return value means either they literally returned nothing, with the intention
                // of manipulating the buttons object manually. Or, they used the button() and
                // the button was not configured in the field so it was stripped out.
                if (! addedButtons) return;

                buttons = buttons.concat(
                    Array.isArray(addedButtons) ? addedButtons : [addedButtons]
                );
            });

            // Remove any nulls. This could happen if a developer-added button was not specified in this field's buttons array.
            buttons = buttons.filter(button => !!button);

            // Remove any non-objects. This would happen if you configure a button name that doesn't exist.
            buttons = buttons.filter(button => typeof button != 'string');

            // Generate fallback html for each button
            buttons = addButtonHtml(buttons);

            // Remove buttons that don't pass conditions.
            // eg. only the insert asset button can be shown if a container has been set.
            buttons = buttons.filter(button => {
                return (button.condition) ? button.condition.call(null, this.config) : true;
            });

            if (_.findWhere(buttons, {name: 'table'})) {
                buttons.push(
                    { name: 'deletetable', text: __('Delete Table'), command: (editor) => editor.commands.deleteTable(), svg: 'delete-table', visibleWhenActive: 'table' },
                    { name: 'addcolumnbefore', text: __('Add Column Before'), command: (editor) => editor.commands.addColumnBefore(), svg: 'add-col-before', visibleWhenActive: 'table' },
                    { name: 'addcolumnafter', text: __('Add Column After'), command: (editor) => editor.commands.addColumnAfter(), svg: 'add-col-after', visibleWhenActive: 'table' },
                    { name: 'deletecolumn', text: __('Delete Column'), command: (editor) => editor.commands.deleteColumn(), svg: 'delete-col', visibleWhenActive: 'table' },
                    { name: 'addrowbefore', text: __('Add Row Before'), command: (editor) => editor.commands.addRowBefore(), svg: 'add-row-before', visibleWhenActive: 'table' },
                    { name: 'addrowafter', text: __('Add Row After'), command: (editor) => editor.commands.addRowAfter(), svg: 'add-row-after', visibleWhenActive: 'table' },
                    { name: 'deleterow', text: __('Delete Row'), command: (editor) => editor.commands.deleteRow(), svg: 'delete-row', visibleWhenActive: 'table' },
                    { name: 'toggleheadercell', text: __('Toggle Header Cell'), command: (editor) => editor.commands.toggleHeaderCell(), svg: 'flip-vertical', visibleWhenActive: 'table' },
                    { name: 'togglecellmerge', text: __('Merge Cells'), command: (editor) => editor.commands.mergeCells(), svg: 'combine-cells', visibleWhenActive: 'table' },
                )
            }

            this.buttons = buttons;
        },

        buttonIsActive(button) {
            if (button.hasOwnProperty('active')) {
                return button.active(this.editor, button.args);
            }
            const nameProperty = button.hasOwnProperty('activeName') ? 'activeName' : 'name';
            const name = button[nameProperty];
            return this.editor.isActive(name, button.args);
        },

        buttonIsVisible(button) {
            if (button.hasOwnProperty('visible')) {
                return button.visible(this.editor, button.args);
            }
            if (! button.hasOwnProperty('visibleWhenActive')) return true;
            return this.editor.isActive(button.visibleWhenActive, button.args);
        },

        visibleButtons(buttons) {
            return buttons.filter(button => this.buttonIsVisible(button));
        },

        initEditor() {
            if (this.editor) this.editor.destroy();

            const content = this.valueToContent(clone(this.value));

            this.editor = new Editor({
                extensions: this.getExtensions(),
                content: content,
                editable: !this.readOnly,
                enableInputRules: this.config.enable_input_rules,
                enablePasteRules: this.config.enable_paste_rules,
                editorProps: { attributes: { class: 'bard-content' }},
                onFocus: () => this.$emit('focus'),
                onBlur: () => {
                    // Since clicking into a field inside a set would also trigger a blur, we can't just emit the
                    // blur event immediately. We need to make sure that the newly focused element is outside
                    // of Bard. We use a timeout because activeElement only exists after the blur event.
                    setTimeout(() => {
                        if (!this.$refs.container.contains(document.activeElement)) {
                            this.$emit('blur');
                            this.showAddSetButton = false;
                        }
                    }, 1);
                },
                onUpdate: () => {
                    this.json = clone(this.editor.getJSON().content);
                    this.html = this.editor.getHTML();
                },
                onCreate: ({ editor }) => {
                    const state = editor.view.state;
                    if (content !== null && typeof content === 'object') {
                        try {
                            state.schema.nodeFromJSON(content);
                        } catch (error) {
                            const invalidError = this.invalidError(error);
                            if (invalidError) {
                                this.initError = invalidError;
                            } else {
                                this.initError = __('Something went wrong');
                                console.error(error);
                            }
                        }
                    }
                }
            });
        },

        invalidError(error) {
            const messages = {
                'Invalid text node in JSON': 'Invalid content, text values must be strings',
                'Empty text nodes are not allowed': 'Invalid content, text values cannot be empty',
            };

            if (messages[error.message]) {
                return __(messages[error.message]);
            }

            let match;
            if (match = error.message.match(/^(?:There is no|Unknown) (?:node|mark) type:? (\w*)(?: in this schema)?$/)) {
                if (match[1]) {
                    return __('Invalid content, :type button/extension is not enabled', { type: match[1] });
                } else {
                    return __('Invalid content, nodes and marks must have a type');
                }
            }
        },

        valueToContent(value) {
            return value.length
                ? { type: 'doc', content: value }
                : null;
        },

        getExtensions() {
            let modeExts = this.inputIsInline ? [DocumentInline] : [DocumentBlock, HardBreak];

            if (this.config.inline === 'break') {
                modeExts.push(HardBreak.extend({
                    addKeyboardShortcuts() {
                        return {
                            ...this.parent?.(),
                            'Enter': () => this.editor.commands.setHardBreak(),
                        }
                    },
                }));
            }

            if (this.config.placeholder) {
                modeExts.push(Placeholder.configure({ placeholder: __(this.config.placeholder) }));
            }

            // Allow passthrough of Ctrl/Cmd + Enter to submit the form
            const DisableCtrlEnter = Extension.create({
                addKeyboardShortcuts() {
                    return {
                        'Ctrl-Enter': () => true,
                        'Cmd-Enter': () => true,
                    }
                },
            });

            let exts = [
                CharacterCount.configure({ limit: this.config.character_limit }),
                ...modeExts,
                DisableCtrlEnter,
                Dropcursor,
                Gapcursor,
                History,
                Paragraph,
                Set.configure({ bard: this }),
                Text
            ];

            if (this.config.smart_typography) {
                exts.push(Typography);
            }

            let btns = this.buttons.map(button => button.name);

            if (btns.includes('anchor')) exts.push(Link.configure({ vm: this }));
            if (btns.includes('bold')) exts.push(Bold);
            if (btns.includes('code')) exts.push(Code);
            if (btns.includes('codeblock')) exts.push(CodeBlockLowlight.configure({ lowlight }));
            if (btns.includes('horizontalrule')) exts.push(HorizontalRule);
            if (btns.includes('image')) exts.push(Image.configure({ bard: this }));
            if (btns.includes('italic')) exts.push(Italic);
            if (btns.includes('quote')) exts.push(Blockquote);
            if (btns.includes('orderedlist')) exts.push(OrderedList);
            if (btns.includes('orderedlist') || btns.includes('unorderedlist')) exts.push(ListItem);
            if (btns.includes('underline')) exts.push(Underline);
            if (btns.includes('unorderedlist')) exts.push(BulletList);
            if (btns.includes('small')) exts.push(Small);
            if (btns.includes('strikethrough')) exts.push(Strike);
            if (btns.includes('subscript')) exts.push(Subscript);
            if (btns.includes('superscript')) exts.push(Superscript);

            let levels = [];
            if (btns.includes('h1')) levels.push(1);
            if (btns.includes('h2')) levels.push(2);
            if (btns.includes('h3')) levels.push(3);
            if (btns.includes('h4')) levels.push(4);
            if (btns.includes('h5')) levels.push(5);
            if (btns.includes('h6')) levels.push(6);
            if (levels.length) exts.push(Heading.configure({ levels }));

            let alignmentTypes = ['paragraph'];
            if (levels.length) alignmentTypes.push('heading');

            let alignments = [];
            if (btns.includes('alignleft')) alignments.push('left');
            if (btns.includes('aligncenter')) alignments.push('center');
            if (btns.includes('alignright')) alignments.push('right');
            if (btns.includes('alignjustify')) alignments.push('justify');
            if (alignments.length) exts.push(TextAlign.configure({ types: alignmentTypes, alignments }));

            if (btns.includes('table')) {
                exts.push(
                    Table.configure({ resizable: true }),
                    TableHeader,
                    TableCell,
                    TableRow,
                );
            }

            this.$bard.extensionCallbacks.forEach((callback) => {
                let returned = callback({ bard: this});
                exts = exts.concat(
                    Array.isArray(returned) ? returned : [returned]
                );
            });

            this.$bard.extensionReplacementCallbacks.forEach(({ callback, name }) => {
                let index = exts.findIndex(ext => ext.name === name);
                if (index === -1) return;
                let extension = exts[index];
                let newExtension = callback({ bard: this, extension });
                exts[index] = newExtension;
            });

            return exts;
        },

        updateSetPreviews(set, previews) {
            this.previews[set] = previews;
        },

        ignorePageHeader(ignore) {
            if (this.pageHeader) {
                this.pageHeader.style['pointer-events'] = ignore ? 'none' : 'all';
            }
        },

        makeBardProvide() {
            const bard = {};
            Object.defineProperties(bard, {
                setConfigs: { get: () => this.setConfigs },
                isReadOnly: { get: () => this.readOnly },
            });
            return bard;
        },

        addSetButtonClicked() {
            if (this.setConfigs.length === 1) {
                this.addSet(this.setConfigs[0].handle);
            }
        },

        clickedAwayFromSetPicker($event) {
            if (this.$el.contains($event.target)) return;
            this.showAddSetButton = false;
        },

    }
}
</script>
