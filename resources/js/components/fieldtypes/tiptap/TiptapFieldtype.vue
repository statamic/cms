<template>

    <div class="tiptap-fieldtype-wrapper">

        <editor-menu-bubble :editor="editor">
            <div
                slot-scope="{ commands, isActive, menu }"
                class="tiptap-toolbar"
                :class="{ 'active': menu.isActive }"
                :style="`left: ${menu.left}px; bottom: ${menu.bottom}px;`"
            >
                <button
                    :class="{ 'active': isActive.bold() }"
                    @click="commands.bold"
                    v-text="'B'" />
            </div>
        </editor-menu-bubble>

        <editor-floating-menu :editor="editor">
            <div
                slot-scope="{ commands, isActive, menu }"
                class="absolute text-2xs"
                :class="{
                    'invisible': !menu.isActive,
                    'visible': menu.isActive
                }"
                :style="`top: ${menu.top}px`"
            >
                <div class="bard-set-selector">
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
            </div>
        </editor-floating-menu>

        <div class="tiptap-field-options no-select">
            <a @click="toggleSource" :class="{ active: showSource }" v-if="allowSource"><i class="icon icon-code"></i></a>
            <a @click="toggleFullscreen"><i class="icon" :class="{ 'icon-resize-full-screen' : ! fullScreenMode, 'icon-resize-100' : fullScreenMode }"></i></a>
        </div>

        <editor-content :editor="editor" />

        {{ html }}

        <pre class="whitespace-pre-wrap">{{ json }}</pre>
    </div>

</template>

<script>
import { Editor, EditorContent, EditorFloatingMenu, EditorMenuBubble } from 'tiptap';
import { Bold, Italic, HardBreak, Heading, History } from 'tiptap-extensions';
import Set from './Set';

export default {

    mixins: [Fieldtype],

    components: {
        EditorContent,
        EditorFloatingMenu,
        EditorMenuBubble,
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
            allowSource: true, // todo
            showSource: false, // todo
            fullScreenMode: false, // todo
        }
    },

    mounted() {
        this.editor = new Editor({
            extensions: [
                new HardBreak(),
                new Bold(),
                new Italic(),
                new Heading({ levels: [2] }),
                new Set(),
                new History(),
            ],
            content: { type: 'doc', content: this.value },
            onUpdate: ({ getJSON, getHTML }) => {
                let value = getJSON().content;
                // Use a json string otherwise Laravel's TrimStrings middleware will remove spaces where we need them.
                value = JSON.stringify(value);
                this.update(value);
            },
        });
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

            this.editor.commands.set({ values, config });
            this.$refs.setSelectorDropdown.close();
        },

        toggleSource() {
            // todo
        },

        toggleFullscreen() {
            // todo
        }

    }
}
</script>


<style lang="scss">
.ProseMirror {
    outline: 0;
}
</style>