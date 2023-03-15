<template>

    <div
        class="bard-fieldtype-wrapper"
        :class="{'bard-fullscreen': fullScreenMode }"
        @dragstart.stop="ignorePageHeader(true)"
        @dragend="ignorePageHeader(false)"
    >

        <div class="bard-fixed-toolbar" v-if="!readOnly && showFixedToolbar">
            <div class="flex flex-wrap items-center no-select" v-if="toolbarIsFixed">
                <component
                    v-for="button in visibleButtons(buttons)"
                    :key="button.name"
                    :is="button.component || 'BardToolbarButton'"
                    :button="button"
                    :active="buttonIsActive(button)"
                    :config="config"
                    :bard="_self"
                    :editor="editor" />
            </div>
            <div class="flex items-center no-select">
                <div class="h-10 -my-sm border-l pr-1 w-px" v-if="toolbarIsFixed && hasExtraButtons"></div>
                <button class="bard-toolbar-button" @click="showSource = !showSource" v-if="allowSource" v-tooltip="__('Show HTML Source')" :aria-label="__('Show HTML Source')">
                    <svg-icon name="file-code" class="w-4 h-4 "/>
                </button>
                <button class="bard-toolbar-button" @click="toggleCollapseSets" v-tooltip="__('Expand/Collapse Sets')" :aria-label="__('Expand/Collapse Sets')" v-if="config.collapse !== 'accordion' && config.sets.length > 0">
                    <svg-icon name="expand-collapse-vertical" class="w-4 h-4" />
                </button>
                <button class="bard-toolbar-button" @click="toggleFullscreen" v-tooltip="__('Toggle Fullscreen Mode')" aria-label="__('Toggle Fullscreen Mode')" v-if="config.fullscreen">
                    <svg-icon name="shrink-all" class="w-4 h-4" v-if="fullScreenMode" />
                    <svg-icon name="expand" class="w-4 h-4" v-else />
                </button>
            </div>
        </div>

        <div class="bard-editor" :class="{ 'mode:read-only': readOnly, 'mode:minimal': ! showFixedToolbar, 'mode:inline': inputIsInline }" tabindex="0">
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

            <floating-menu class="bard-set-selector" :editor="editor" :tippy-options="{ offset: calcFloatingOffset, zIndex: 6 }" :should-show="shouldShowSetButton" v-if="editor">
                <dropdown-list>
                    <template v-slot:trigger>
                        <button type="button" class="btn-round" :aria-label="__('Add Set')" v-tooltip="__('Add Set')">
                            <span class="icon icon-plus text-grey-80 antialiased"></span>
                        </button>
                    </template>

                    <div v-for="set in config.sets" :key="set.handle">
                        <dropdown-item :text="set.display || set.handle" @click="addSet(set.handle)" />
                    </div>
                </dropdown-list>
            </floating-menu>

            <div class="bard-invalid" v-if="invalid" v-html="__('Invalid content')"></div>
            <editor-content :editor="editor" v-show="!showSource" :id="fieldId" />
            <bard-source :html="htmlWithReplacedLinks" v-if="showSource" />
        </div>
        <div class="bard-footer-toolbar" v-if="editor && (config.reading_time || config.character_limit)">
            <div v-if="config.reading_time">{{ readingTime }} {{ __('Reading Time') }}</div>
            <div v-else />

            <div v-if="config.character_limit">{{ editor.storage.characterCount.characters() }}/{{ config.character_limit }}</div>
        </div>
    </div>

</template>

<script>
import uniqid from 'uniqid';
import { BubbleMenu, Editor, EditorContent, FloatingMenu } from '@tiptap/vue-2';
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
        EditorContent,
        FloatingMenu,
        LinkToolbarButton,
    },

    provide() {
        return {
            setConfigs: this.config.sets,
            isReadOnly: this.readOnly,
        }
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
            invalid: false,
            pageHeader: null,
            escBinding: null,
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
            return this.allowSource || this.config.sets.length > 0 || this.config.fullscreen;
        },

        readingTime() {
            if (this.html) {
                var stats = readTimeEstimate(this.html, 265, 12, 500, ['img', 'Image', 'bard-set']);
                var duration = moment.duration(stats.duration, 'minutes');

                return moment.utc(duration.asMilliseconds()).format("mm:ss");
            }
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
            const stack = JSON.parse(this.value);
            let text = '';
            while (stack.length) {
                const node = stack.shift();
                if (node.type === 'text') {
                    text += ` ${node.text || ''}`;
                } else if (node.type === 'set') {
                    const handle = node.attrs.values.type;
                    const set = this.config.sets.find(set => set.handle === handle);
                    text += ` [${set ? set.display : handle}]`;
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

    },

    mounted() {
        this.initToolbarButtons();

        const content = this.valueToContent(clone(this.value));

        this.editor = new Editor({
            extensions: this.getExtensions(),
            content: content,
            editable: !this.readOnly,
            enableInputRules: this.config.enable_input_rules,
            enablePasteRules: this.config.enable_paste_rules,
            onFocus: () => this.$emit('focus'),
            onBlur: () => {
                // Since clicking into a field inside a set would also trigger a blur, we can't just emit the
                // blur event immediately. We need to make sure that the newly focused element is outside
                // of Bard. We use a timeout because activeElement only exists after the blur event.
                setTimeout(() => {
                    if (!this.$el.contains(document.activeElement)) this.$emit('blur');
                }, 1);
            },
            onUpdate: () => {
                this.json = this.editor.getJSON().content;
                this.html = this.editor.getHTML();
            },
            onCreate: ({ editor }) => {
                const state = editor.view.state;
                 if (content !== null && typeof content === 'object') {
                     try {
                         state.schema.nodeFromJSON(content);
                     } catch (error) {
                         this.invalid = true;
                     }
                 }
            }
        });

        this.json = this.editor.getJSON().content;
        this.html = this.editor.getHTML();

        this.escBinding = this.$keys.bind('esc', this.closeFullscreen)

        this.$nextTick(() => {
            this.mounted = true;
            if (this.config.collapse) this.collapseAll();
        });

        this.pageHeader = document.querySelector('.global-header');

        this.$store.commit(`publish/${this.storeName}/setFieldSubmitsJson`, this.fieldPathPrefix || this.handle);
    },

    beforeDestroy() {
        this.editor.destroy();
        this.escBinding.destroy();

        this.$store.commit(`publish/${this.storeName}/unsetFieldSubmitsJson`, this.fieldPathPrefix || this.handle);
    },

    watch: {

        json(json) {
            if (!this.mounted) return;

            // Prosemirror's JSON will include spaces between tags.
            // For example (this is not the actual json)...
            // "<p>One <b>two</b> three</p>" becomes ['OneSPACE', '<b>two</b>', 'SPACEthree']
            // But, Laravel's TrimStrings middleware would remove them.
            // Those spaces need to be there, otherwise it would be rendered as <p>One<b>two</b>three</p>
            // To combat this, we submit the JSON string instead of an object.
            this.updateDebounced(JSON.stringify(json));
        },

        value(value, oldValue) {
            if (value === oldValue) return;

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

        fieldPathPrefix(fieldPathPrefix, oldFieldPathPrefix) {
            this.$store.commit(`publish/${this.storeName}/unsetFieldSubmitsJson`, oldFieldPathPrefix);
            this.$store.commit(`publish/${this.storeName}/setFieldSubmitsJson`, fieldPathPrefix);
        },

    },

    methods: {
        addSet(handle) {
            const id = uniqid();
            const values = Object.assign({}, { type: handle }, this.meta.defaults[handle]);

            let previews = {};
            Object.keys(this.meta.defaults[handle]).forEach(key => previews[key] = null);
            this.previews = Object.assign({}, this.previews, { [id]: previews });

            this.updateSetMeta(id, this.meta.new[handle]);

            // Perform this in nextTick because the meta data won't be ready until then.
            this.$nextTick(() => {
                this.editor.commands.set({ id, values });
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
            this.$root.hideOverflow = ! this.$root.hideOverflow;
        },

        closeFullscreen() {
            this.fullScreenMode = false;
            this.$root.hideOverflow = false;
        },

        shouldShowSetButton({ view, state }) {
            const { selection } = state;
            const { $anchor, empty } = selection;
            const isRootDepth = $anchor.depth === 1;
            const isEmptyTextBlock = $anchor.parent.isTextblock && !$anchor.parent.type.spec.code && !$anchor.parent.textContent;

            const isActive = view.hasFocus() && empty && isRootDepth && isEmptyTextBlock;
            return this.config.sets.length && (this.config.always_show_set_button || isActive);
        },

        calcFloatingOffset({ reference }) {
            let x = reference.x + reference.width + 20;
            return [0, -x];
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

        valueToContent(value) {
            // A json string is passed from PHP since that's what's submitted.
            value = JSON.parse(value);

            return value.length
                ? { type: 'doc', content: value }
                : null;
        },

        getExtensions() {
            let exts = [
                CharacterCount.configure({ limit: this.config.character_limit }),
                ...(this.inputIsInline ? [DocumentInline] : [DocumentBlock, HardBreak]),
                Dropcursor,
                Gapcursor,
                History,
                Paragraph,
                Placeholder.configure({ placeholder: this.config.placeholder }),
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

            let alignments = [];
            if (btns.includes('alignleft')) alignments.push('left');
            if (btns.includes('aligncenter')) alignments.push('center');
            if (btns.includes('alignright')) alignments.push('right');
            if (btns.includes('alignjustify')) alignments.push('justify');
            if (alignments.length) exts.push(TextAlign.configure({ types: ['heading', 'paragraph'], alignments }));

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
        }

    }
}
</script>
