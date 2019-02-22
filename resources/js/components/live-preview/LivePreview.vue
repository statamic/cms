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
                        <div class="text-lg font-medium mr-2">{{ __('Live Preview') }}</div>
                        <label v-if="ampEnabled"><input type="checkbox" v-model="amp" /> AMP</label>
                        <div class="flex">
                            <div class="select-input-container w-32 mr-1">
                                <select class="select-input" v-model="previewDevice">
                                    <option :value="device" v-text="device" :selected="previewDevice === device" v-for="device in previewDevices"></option>
                                </select>
                                <div class="select-input-toggle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            <button class="btn" @click="close">{{ __('Close') }}</button>
                        </div>
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
                            <div class="flex items-start justify-center h-full w-full">
                                <iframe ref="iframe" frameborder="0" :class="previewDevice" />
                            </div>
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

let source;
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
        blueprint: String,
    },

    data() {
        return {
            panesVisible: false,
            headerVisible: false,
            editorWidth: null,
            previewDevice: 'Responsive',
            amp: false,
            ampEnabled: Statamic.ampEnabled,
            previewDevices: ['Responsive', 'Laptop', 'Tablet', 'Mobile'],
            provides: {
                storeName: this.name
            },
        }
    },

    computed: {

        payload() {
            return {
                amp: this.amp,
                blueprint: this.blueprint,
                preview: this.values
            }
        },
    },

    watch: {

        previewing(enabled) {
            if (!enabled) return;

            this.update();
            this.animateIn();
        },

        payload: {
            deep: true,
            handler(payload) {
                if (this.previewing) this.update();
            }
        }

    },

    created() {
        this.editorWidth = localStorage.getItem(widthLocalStorageKey) || 400

        this.$mousetrap.bindGlobal('meta+shift+p', () => {
            this.previewing ? this.close() : this.$emit('opened-via-keyboard');
        });
    },

    destroyed() {
        this.$mousetrap.unbind('meta+shift+p');
    },

    methods: {

        update: _.debounce(function () {
            if (source) source.cancel();
            source = axios.CancelToken.source();

            axios.post(this.url, this.payload, { cancelToken: source.token }).then(response => {
                this.updateIframeContents(response.data);
            }).catch(e => {
                if (axios.isCancel(e)) return;
                throw e;
            });
        }, 150),

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

        setEditorWidth(width) {
            this.editorWidth = width;
            localStorage.setItem(widthLocalStorageKey, width);
        }
    }

}
</script>
