<template>

    <div class="bard-fieldtype-wrapper" :class="{'bard-fullscreen': fullScreenMode }">

        <editor-menu-bar :editor="editor">
            <div slot-scope="{ commands, isActive, menu }" class="bard-fixed-toolbar">
                <div class="flex items-center no-select" v-if="toolbarIsFixed">
                    <button
                        v-for="button in buttons"
                        :key="button.name"
                        :class="{ 'active': isActive[button.command](button.args) }"
                        v-tooltip="button.text"
                        @click="commands[button.command](button.args)"
                        v-html="button.html" />
                </div>
                <div class="flex items-center no-select">
                    <button @click="showSource = !showSource" v-if="allowSource"><i class="icon icon-code"></i></button>
                    <button @click="toggleFullscreen"><i class="icon" :class="{ 'icon-resize-full-screen' : ! fullScreenMode, 'icon-resize-100' : fullScreenMode }"></i></button>
                </div>
            </div>
        </editor-menu-bar>

        <div class="bard-editor">
            <editor-menu-bubble :editor="editor" v-if="toolbarIsFloating">
                <div
                    slot-scope="{ commands, isActive, menu }"
                    class="bard-floating-toolbar"
                    :class="{ 'active': menu.isActive }"
                    :style="`left: ${menu.left}px; bottom: ${menu.bottom}px;`"
                >
                    <button
                        v-for="button in buttons"
                        :key="button.name"
                        :class="{ 'active': isActive[button.command](button.args) }"
                        v-tooltip="button.text"
                        @click="commands[button.command](button.args)"
                        v-html="button.html" />
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
                        <button type="button" class="btn btn-round" slot="trigger">
                            <span class="icon icon-plus text-grey-80 antialiased"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li v-for="set in config.sets" :key="set.handle">
                                <a @click="addSet(set.handle)" v-text="set.display || set.handle" />
                            </li>
                        </ul>
                    </dropdown-list>
                </div>
            </editor-floating-menu>

            <editor-content :editor="editor" v-show="!showSource" />

            <bard-source :html="html" v-if="showSource" />
        </div>
    </div>

</template>

<script>
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
    History
} from 'tiptap-extensions';
import Set from './Set';
import BardSource from './Source.vue';
import ConfirmSetDelete from './ConfirmSetDelete';
import { availableButtons, addButtonHtml } from '../bard/buttons';

export default {

    mixins: [Fieldtype],

    components: {
        EditorContent,
        EditorMenuBar,
        EditorFloatingMenu,
        EditorMenuBubble,
        BardSource,
    },

    provide() {
        return {
            setConfigs: this.config.sets
        }
    },

    data() {
        return {
            editor: null,
            html: null,
            json: null,
            showSource: false,
            fullScreenMode: false, // todo
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

    },

    mounted() {
        this.initToolbarButtons();

        // A json string is passed from PHP since that's what's submitted.
        const value = JSON.parse(this.value);

        let content = value.length
            ? { type: 'doc', content: value }
            : null;

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
                new History(),
                new Set(),
                new ConfirmSetDelete(),
            ],
            content,
            onUpdate: ({ getJSON, getHTML }) => {
                let value = getJSON().content;
                // Use a json string otherwise Laravel's TrimStrings middleware will remove spaces where we need them.
                value = JSON.stringify(value);
                this.update(value);
                this.html = getHTML();
            },
        });

        this.html = this.editor.getHTML();
    },

    beforeDestroy() {
        this.editor.destroy();
    },

    methods: {

        addSet(handle) {
            const config = _.find(this.config.sets, { handle }) || {};

            let values = {type: handle};

            _.each(config.fields, field => {
                values[field.handle] = field.default
                // || Statamic.fieldtypeDefaults[field.type] // TODO
                || null;
            });

            this.editor.commands.set({ values });
            this.$refs.setSelectorDropdown.close();
        },

        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
            this.$root.hideOverflow = ! this.$root.hideOverflow;
        },

        initToolbarButtons() {
            const selectedButtons = this.config.buttons || [
                'h2', 'h3', 'bold', 'italic', 'unorderedlist', 'orderedlist', 'removeformat', 'quote', 'anchor',
            ];

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

            this.buttons = buttons;
        }

    }
}
</script>
