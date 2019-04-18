<template>

    <div class="bard-fieldtype-wrapper" :class="{'bard-fullscreen': fullScreenMode }">

        <editor-menu-bar :editor="editor">
            <div slot-scope="{ commands, isActive, menu }" class="bard-fixed-toolbar">
                <div class="flex items-center no-select" v-if="toolbarIsFixed">
                    <component
                        v-for="button in buttons"
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
                    <button @click="toggleFullscreen" v-tooltip="__('Toggle Fullscreen Mode')">
                        <svg-icon name="shrink" class="w-4 h-4" v-if="fullScreenMode" />
                        <svg-icon name="expand" class="w-4 h-4" v-else />
                    </button>
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
                    <component
                        v-for="button in buttons"
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
        <div class="bard-footer-toolbar">
            {{ readingTime }} {{ __('Reading Time') }}
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
import Link from './Link';
import Image from './Image';
import RemoveFormat from './RemoveFormat';
import LinkToolbarButton from './LinkToolbarButton.vue';
import ConfirmSetDelete from './ConfirmSetDelete';
import { availableButtons, addButtonHtml } from '../bard/buttons';
import readTimeEstimate from 'read-time-estimate';

export default {

    mixins: [Fieldtype],

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

        readingTime() {
            if (this.html) {
                var stats = readTimeEstimate(this.html, 265, 12, 500, ['img', 'Image', 'bard-set']);
                var duration = moment.duration(stats.duration, 'minutes');

                return moment.utc(duration.asMilliseconds()).format("mm:ss");
            }
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
                new History(),
                new Set(),
                new ConfirmSetDelete(),
                new Link({ vm: this }),
                new RemoveFormat(),
                new Image({ bard: this }),
            ],
            content: this.valueToContent(this.value),
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

    watch: {

        value(value, oldValue) {
            if (value === oldValue) return;

            const oldContent = this.editor.getJSON();
            const content = this.valueToContent(value);

            if (JSON.stringify(content) !== JSON.stringify(oldContent)) {
                this.editor.setContent(content);
            }
        }

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
        },

        buttonIsActive(isActive, button) {
            if (! isActive.hasOwnProperty(button.command)) return false;
            return isActive[button.command](button.args);
        },

        valueToContent(value) {
            // A json string is passed from PHP since that's what's submitted.
            value = JSON.parse(this.value);

            return value.length
                ? { type: 'doc', content: value }
                : null;
        }
    }
}
</script>
