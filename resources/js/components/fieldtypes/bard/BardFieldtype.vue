<template>

    <div
        class="bard-fieldtype-wrapper"
        :class="{'bard-fullscreen': fullScreenMode }"
        @dragstart.stop="ignorePageHeader(true)"
        @dragend="ignorePageHeader(false)"
    >

        <editor-menu-bar :editor="editor" v-if="!readOnly">
            <div slot-scope="{ commands, isActive, menu }" class="bard-fixed-toolbar" v-if="showFixedToolbar">
                <div class="flex flex-wrap items-center no-select" v-if="toolbarIsFixed">
                    <component
                        v-for="button in visibleButtons(buttons, isActive)"
                        :key="button.name"
                        :is="button.component || 'BardToolbarButton'"
                        :button="button"
                        :active="buttonIsActive(isActive, button)"
                        :config="config"
                        :bard="_self"
                        :editor="editor" />
                </div>
                <div class="flex items-center no-select">
                <div class="h-10 -my-sm border-l pr-1 w-px" v-if="toolbarIsFixed && hasExtraButtons"></div>
                    <button class="bard-toolbar-button" @click="showSource = !showSource" v-if="allowSource" v-tooltip="__('Show HTML Source')" :aria-label="__('Show HTML Source')">
                        <svg-icon name="file-code" class="w-4 h-4 "/>
                    </button>
                    <button class="bard-toolbar-button" @click="toggleCollapseSets" v-tooltip="__('Expand/Collapse Sets')" :aria-label="__('Expand/Collapse Sets')" v-if="config.sets.length > 0">
                        <svg-icon name="expand-collapse-vertical" class="w-4 h-4" />
                    </button>
                    <button class="bard-toolbar-button" @click="toggleFullscreen" v-tooltip="__('Toggle Fullscreen Mode')" aria-label="__('Toggle Fullscreen Mode')" v-if="config.fullscreen">
                        <svg-icon name="shrink-all" class="w-4 h-4" v-if="fullScreenMode" />
                        <svg-icon name="expand" class="w-4 h-4" v-else />
                    </button>
                </div>
            </div>
        </editor-menu-bar>

        <div class="bard-editor" :class="{ 'mode:read-only': readOnly, 'mode:minimal': ! showFixedToolbar }" tabindex="0">
            <editor-menu-bubble :editor="editor" v-if="toolbarIsFloating && !readOnly">
                <div
                    slot-scope="{ commands, isActive, menu }"
                    class="bard-floating-toolbar"
                    :class="{ 'active': menu.isActive }"
                    :style="`left: ${menu.left}px; bottom: ${menu.bottom}px;`"
                >
                    <component
                        v-for="button in visibleButtons(buttons, isActive)"
                        :key="button.name"
                        :is="button.component || 'BardToolbarButton'"
                        :button="button"
                        :active="buttonIsActive(isActive, button)"
                        :bard="_self"
                        :config="config"
                        :editor="editor" />
                </div>
            </editor-menu-bubble>

            <editor-floating-menu :editor="editor">
                <div
                    slot-scope="{ menu }"
                    class="bard-set-selector"
                    :class="config.sets.length && (config.always_show_set_button || menu.isActive) ? 'visible' : 'invisible'"
                    :style="`top: ${menu.top}px`"
                >
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
                </div>
            </editor-floating-menu>

            <editor-content :editor="editor" v-show="!showSource" :id="fieldId" />
            <bard-source :html="htmlWithReplacedLinks" v-if="showSource" />
        </div>
        <div class="bard-footer-toolbar" v-if="config.reading_time">
            {{ readingTime }} {{ __('Reading Time') }}
        </div>
    </div>

</template>

<script>
import uniqid from 'uniqid';
import { Editor, EditorContent, EditorMenuBar, EditorFloatingMenu, EditorMenuBubble, Paragraph, Text } from 'tiptap';
import {
    Blockquote,
    CodeBlock,
    HardBreak,
    Heading,
    HorizontalRule,
    OrderedList,
    BulletList,
    ListItem,
    Bold,
    Code,
    Italic,
    Strike,
    Underline,
    Table,
    TableHeader,
    TableCell,
    TableRow,
    History,
    CodeBlockHighlight
} from 'tiptap-extensions';
import Set from './Set';
import Doc from './Doc';
import BardSource from './Source.vue';
import Link from './Link';
import Image from './Image';
import Small from './Small';
import Subscript from './Subscript';
import Superscript from './Superscript';
import RemoveFormat from './RemoveFormat';
import LinkToolbarButton from './LinkToolbarButton.vue';
import ManagesSetMeta from '../replicator/ManagesSetMeta';
import { availableButtons, addButtonHtml } from '../bard/buttons';
import readTimeEstimate from 'read-time-estimate';
import javascript from 'highlight.js/lib/languages/javascript'
import css from 'highlight.js/lib/languages/css'
import hljs from 'highlight.js/lib/highlight';
import 'highlight.js/styles/github.css';
import mark from './Mark';
import node from './Node';

export default {

    mixins: [Fieldtype, ManagesSetMeta],

    components: {
        EditorContent,
        EditorMenuBar,
        EditorFloatingMenu,
        EditorMenuBubble,
        BardSource,
        BardToolbarButton,
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

    },

    mounted() {
        this.initToolbarButtons();

        this.editor = new Editor({
            useBuiltInExtensions: false,
            extensions: this.getExtensions(),
            content: this.valueToContent(clone(this.value)),
            editable: !this.readOnly,
            disableInputRules: ! this.config.enable_input_rules,
            disablePasteRules: ! this.config.enable_paste_rules,
            onFocus: () => this.$emit('focus'),
            onBlur: () => {
                // Since clicking into a field inside a set would also trigger a blur, we can't just emit the
                // blur event immediately. We need to make sure that the newly focused element is outside
                // of Bard. We use a timeout because activeElement only exists after the blur event.
                setTimeout(() => {
                    if (!this.$el.contains(document.activeElement)) this.$emit('blur');
                }, 1);
            },
            onUpdate: ({ getJSON, getHTML }) => {
                this.json = getJSON().content;
                this.html = getHTML();
            },
        });

        this.json = this.editor.getJSON().content;
        this.html = this.editor.getHTML();

        this.escBinding = this.$keys.bind('esc', this.closeFullscreen)

        this.$nextTick(() => this.mounted = true);

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
                this.editor.clearContent()
                this.editor.setContent(content, true);
            }
        },

        readOnly(readOnly) {
            this.editor.setOptions({ editable: !this.readOnly });
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
            const id = `set-${uniqid()}`;
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
            const id = `set-${uniqid()}`;
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
                    { name: 'deletetable', text: __('Delete Table'), command: 'deleteTable', svg: 'delete-table', visibleWhenActive: 'table' },
                    { name: 'addcolumnbefore', text: __('Add Column Before'), command: 'addColumnBefore', svg: 'add-col-before', visibleWhenActive: 'table' },
                    { name: 'addcolumnafter', text: __('Add Column After'), command: 'addColumnAfter', svg: 'add-col-after', visibleWhenActive: 'table' },
                    { name: 'deletecolumn', text: __('Delete Column'), command: 'deleteColumn', svg: 'delete-col', visibleWhenActive: 'table' },
                    { name: 'addrowbefore', text: __('Add Row Before'), command: 'addRowBefore', svg: 'add-row-before', visibleWhenActive: 'table' },
                    { name: 'addrowafter', text: __('Add Row After'), command: 'addRowAfter', svg: 'add-row-after', visibleWhenActive: 'table' },
                    { name: 'deleterow', text: __('Delete Row'), command: 'deleteRow', svg: 'delete-row', visibleWhenActive: 'table' },
                    { name: 'toggleheadercell', text: __('Toggle Header Cell'), command: 'toggleHeaderCell', svg: 'flip-vertical', visibleWhenActive: 'table' },
                    { name: 'togglecellmerge', text: __('Merge Cells'), command: 'toggleCellMerge', svg: 'combine-cells', visibleWhenActive: 'table' },
                )
            }

            this.buttons = buttons;
        },

        buttonIsActive(isActive, button) {
            const commandProperty = button.hasOwnProperty('activeCommand') ? 'activeCommand' : 'command';
            const command = button[commandProperty];
            if (! isActive.hasOwnProperty(command)) return false;
            return isActive[command](button.args);
        },

        buttonIsVisible(isActive, button) {
            if (! button.hasOwnProperty('visibleWhenActive')) return true;
            return isActive[button.visibleWhenActive](button.args);
        },

        visibleButtons(buttons, isActive) {
            return buttons.filter(button => this.buttonIsVisible(isActive, button));
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
                new Doc(),
                new Set({ bard: this }),
                new Text(),
                new Paragraph(),
                new HardBreak(),
                new History()
            ];

            let btns = this.buttons.map(button => button.name);

            if (btns.includes('anchor')) exts.push(new Link({ vm: this }));
            if (btns.includes('quote')) exts.push(new Blockquote());
            if (btns.includes('bold')) exts.push(new Bold());
            if (btns.includes('italic')) exts.push(new Italic());
            if (btns.includes('strikethrough')) exts.push(new Strike());
            if (btns.includes('small')) exts.push(new Small());
            if (btns.includes('underline')) exts.push(new Underline());
            if (btns.includes('subscript')) exts.push(new Subscript());
            if (btns.includes('superscript')) exts.push(new Superscript());
            if (btns.includes('removeformat')) exts.push(new RemoveFormat());
            if (btns.includes('image')) exts.push(new Image({ bard: this }));
            if (btns.includes('horizontalrule')) exts.push(new HorizontalRule());

            if (btns.includes('orderedlist') || btns.includes('unorderedlist')) {
                if (btns.includes('orderedlist')) exts.push(new OrderedList());
                if (btns.includes('unorderedlist')) exts.push(new BulletList());
                exts.push(new ListItem());
            }

            if (btns.includes('codeblock') || btns.includes('code')) {
                if (btns.includes('code')) exts.push(new Code());
                if (btns.includes('codeblock')) exts.push(new CodeBlock());
                exts.push(new CodeBlockHighlight({ languages: { javascript, css }}));
            }

            if (btns.includes('table')) {
                exts.push(
                    new Table({ resizable: true }),
                    new TableHeader(),
                    new TableCell(),
                    new TableRow(),
                );
            }

            if (btns.includes('h1') ||
                btns.includes('h2') ||
                btns.includes('h3') ||
                btns.includes('h4') ||
                btns.includes('h5') ||
                btns.includes('h6')
            ) {
                let levels = [];
                if (btns.includes('h1')) levels.push(1);
                if (btns.includes('h2')) levels.push(2);
                if (btns.includes('h3')) levels.push(3);
                if (btns.includes('h4')) levels.push(4);
                if (btns.includes('h5')) levels.push(5);
                if (btns.includes('h6')) levels.push(6);
                exts.push(new Heading({ levels }));
            }

            this.$bard.extensionCallbacks.forEach(callback => {
                let returned = callback({ bard: this, mark, node });
                exts = exts.concat(
                    Array.isArray(returned) ? returned : [returned]
                );
            });

            this.$bard.extensionReplacementCallbacks.forEach(({callback, name}) => {
                let index = exts.findIndex(ext => ext.name === name);
                if (index === -1) return;
                let extension = exts[index];
                let newExtension = callback({ bard: this, mark, node, extension });
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
