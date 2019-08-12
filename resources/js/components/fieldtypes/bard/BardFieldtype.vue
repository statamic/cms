<template>

    <div class="bard-fieldtype-wrapper" :class="{'bard-fullscreen': fullScreenMode }">

        <editor-menu-bar :editor="editor" v-if="!readOnly">
            <div slot-scope="{ commands, isActive, menu }" class="bard-fixed-toolbar">
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
                    <button @click="showSource = !showSource" v-if="allowSource" v-tooltip="__('Show HTML Source')">
                        <svg-icon name="file-code" class="w-4 h-4 "/>
                    </button>
                    <button @click="toggleFullscreen" v-tooltip="__('Toggle Fullscreen Mode')" v-if="config.fullscreen">
                        <svg-icon name="shrink" class="w-4 h-4" v-if="fullScreenMode" />
                        <svg-icon name="expand" class="w-4 h-4" v-else />
                    </button>
                </div>
            </div>
        </editor-menu-bar>

        <div class="bard-editor" :class="{ 'bg-grey-30 text-grey-70': readOnly }" tabindex="0">
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
                    slot-scope="{ commands, isActive, menu }"
                    class="bard-set-selector"
                    :class="{
                        'invisible': !menu.isActive,
                        'visible': menu.isActive
                    }"
                    :style="`top: ${menu.top}px`"
                >
                    <dropdown-list ref="setSelectorDropdown">
                        <template v-slot:trigger>
                            <button type="button" class="btn btn-round">
                                <span class="icon icon-plus text-grey-80 antialiased"></span>
                            </button>
                        </template>

                        <div v-for="set in config.sets" :key="set.handle">
                            <dropdown-item :text="set.display || set.handle" @click="addSet(set.handle)" />
                        </div>
                    </dropdown-list>
                </div>
            </editor-floating-menu>

            <editor-content :editor="editor" v-show="!showSource" />

            <bard-source :html="html" v-if="showSource" />
        </div>
        <div class="bard-footer-toolbar" v-if="config.reading_time">
            {{ readingTime }} {{ __('Reading Time') }}
        </div>
    </div>

</template>

<script>
import uniqid from 'uniqid';
import { Editor, EditorContent, EditorMenuBar, EditorFloatingMenu, EditorMenuBubble } from 'tiptap';
import {
    Blockquote,
    CodeBlock,
    HardBreak,
    Heading,
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
import BardSource from './Source.vue';
import Link from './Link';
import Image from './Image';
import RemoveFormat from './RemoveFormat';
import LinkToolbarButton from './LinkToolbarButton.vue';
import ConfirmSetDelete from './ConfirmSetDelete';
import ManagesSetMeta from '../replicator/ManagesSetMeta';
import { availableButtons, addButtonHtml } from '../bard/buttons';
import readTimeEstimate from 'read-time-estimate';
import javascript from 'highlight.js/lib/languages/javascript'
import css from 'highlight.js/lib/languages/css'
import hljs from 'highlight.js/lib/highlight';
import 'highlight.js/styles/github.css';

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
            setConfigs: this.config.sets
        }
    },

    inject: ['storeName'],

    data() {
        return {
            editor: null,
            html: null,
            json: null,
            showSource: false,
            fullScreenMode: false,
            buttons: [],
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
        }

    },

    mounted() {
        this.initToolbarButtons();

        this.editor = new Editor({
            extensions: [
                new Blockquote(),
                new BulletList(),
                new CodeBlock(),
                new HardBreak(),
                new Heading({ levels: [1, 2, 3, 4, 5, 6] }),
                new ListItem(),
                new OrderedList(),
                new Bold(),
                new Code(),
                new Italic(),
                new Strike(),
                new Underline(),
                new Table({
                    resizable: true,
                }),
                new TableHeader(),
                new TableCell(),
                new TableRow(),
                new History(),
                new Set({ bard: this }),
                new ConfirmSetDelete(),
                new Link({ vm: this }),
                new RemoveFormat(),
                new Image({ bard: this }),
                new CodeBlockHighlight({
                    languages: { javascript, css }
                })
            ],
            content: this.valueToContent(clone(this.value)),
            editable: !this.readOnly,
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
                let value = getJSON().content;
                // Use a json string otherwise Laravel's TrimStrings middleware will remove spaces where we need them.
                value = JSON.stringify(value);
                this.update(value);
                this.html = getHTML();
            },
        });

        this.html = this.editor.getHTML();

        this.$mousetrap.bind('esc', this.closeFullscreen)
    },

    beforeDestroy() {
        this.editor.destroy();
    },

    watch: {

        value(value, oldValue) {
            if (value === oldValue) return;

            const oldContent = this.editor.getJSON();
            const content = this.valueToContent(value);

            if (JSON.stringify(content) !== JSON.stringify(oldContent)) {
                this.editor.setContent(content);
            }
        },

        readOnly(readOnly) {
            this.editor.setOptions({ editable: !this.readOnly });
        }

    },

    methods: {

        addSet(handle) {
            const id = `set-${uniqid()}`;
            const values = Object.assign({}, { type: handle }, this.meta.defaults[handle]);
            this.updateSetMeta(id, this.meta.new[handle]);

            // Perform this in nextTick because the meta data won't be ready until then.
            this.$nextTick(() => {
                this.editor.commands.set({ id, values });
                this.$refs.setSelectorDropdown.close();
            });
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
                    'togglecellmerge'
                );
            }

            // Get the configured buttons and swap them with corresponding objects
            let buttons = selectedButtons.map(button => {
                return _.findWhere(availableButtons(), { name: button.toLowerCase() })
                    || button;
            });

            // Let addons add, remove, or control the position of buttons.
            Statamic.$config.get('bard').buttons.forEach(callback => callback.call(null, buttons));

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
        }
    }
}
</script>
