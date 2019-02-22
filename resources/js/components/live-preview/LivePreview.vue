<template>

    <div>
        <portal to="live-preview-fields" :disabled="!previewing">
            <provider :variables="provides">
                <slot name="default" />
            </provider>
        </portal>

        <portal v-if="previewing" to="live-preview">
            <div class="live-preview fixed flex flex-col">

                <transition name="live-preview-header-slide">
                    <div v-show="headerVisible" class="live-preview-header">
                        <div class="text-lg font-medium mr-2">Live Preview</div>
                        <div>
                            <button class="font-bold mx-1">Desktop</button>
                            <button class="text-blue mx-1">Mobile</button>
                        </div>
                        <button class="text-grey" @click="close">&times;</button>
                    </div>
                </transition>

                <div class="live-preview-main flex flex-1">

                    <transition name="live-preview-editor-slide">
                        <div v-show="panesVisible" class="live-preview-editor" :style="{ width: `${editorWidth}px` }">
                            <div class="live-preview-fields flex-1 h-full overflow-scroll">
                                <portal-target name="live-preview-fields" />
                            </div>

                            <resizer @resized="setEditorWidth" />
                        </div>
                    </transition>

                    <transition name="live-preview-contents-slide">
                        <div v-show="panesVisible" class="live-preview-contents">
                            <iframe ref="iframe" frameborder="0" class="w-full h-full" />
                        </div>
                    </transition>

                </div>

            </div>
        </portal>

    </div>

</template>

<script>
import axios from 'axios';
import Provider from './Provider.vue';
import Resizer from './Resizer.vue';

const widthLocalStorageKey = 'statamic.live-preview.editor-width';

export default {

    components: {
        Provider,
        Resizer
    },

    props: {
        url: String,
        previewing: Boolean,
        values: Object,
        name: String,
    },

    data() {
        return {
            panesVisible: false,
            headerVisible: false,
            editorWidth: null,
            provides: {
                storeName: this.name
            }
        }
    },

    watch: {

        previewing(enabled) {
            if (enabled) {
                this.update();
                this.animateIn();
            }

            let state = this.$store.state.statamic.livePreview;
            state.enabled = enabled;
            this.$store.commit('statamic/livePreview', state);
        },

        values: {
            deep: true,
            handler(values) {
                if (this.previewing) this.update();
            }
        }

    },

    created() {
        this.editorWidth = localStorage.getItem(widthLocalStorageKey) || 400
    },

    methods: {

        update() {
            const payload = {
                preview: this.values
            };

            axios.post(this.url, payload).then(response => {
                this.updateIframeContents(response.data);
            })
        },

        updateIframeContents(contents) {
            const iframe = this.$refs.iframe;
            const scrollX = $(iframe.contentWindow.document).scrollLeft();
            const scrollY = $(iframe.contentWindow.document).scrollTop();

            contents += '<script type="text/javascript">window.scrollTo('+scrollX+', '+scrollY+');\x3c/script>';

            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(contents);
            iframe.contentWindow.document.close();
        },

        close() {
            this.animateOut().then(() => this.$emit('closed'));
        },

        animateIn() {
            return this
                .$wait(100)
                .then(() => {
                    this.headerVisible = true;
                    return this.$wait(200);
                })
                .then(() => this.panesVisible = true);
        },

        animateOut() {
            this.panesVisible = false;
            this.headerVisible = false;
            return this.$wait(300);
        },

        $wait(ms) {
            return new Promise(resolve => {
                setTimeout(resolve, ms);
            });
        },

        setEditorWidth(width) {
            this.editorWidth = width;
            localStorage.setItem(widthLocalStorageKey, width);
        }
    }

}
</script>
