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
                        <div class="flex items-center">
                            <label v-if="amp" class="mr-2"><input type="checkbox" v-model="previewAmp" /> AMP</label>
                            <button class="btn mr-2" @click="popout">Pop out</button>
                            <div class="select-input-container w-32">
                                <select class="select-input" v-model="previewDevice">
                                    <option :value="device" :key="device" v-text="device" :selected="previewDevice === device" v-for="device in previewDevices"></option>
                                </select>
                                <div class="select-input-toggle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                @click="close"
                                v-html="'&times'" />
                        </div>
                    </div>
                </transition>

                <div class="live-preview-main flex flex-1">

                    <transition name="live-preview-editor-slide">
                        <div v-show="panesVisible" class="live-preview-editor" :style="{ width: poppedOut ? '100%' : `${editorWidth}px` }">
                            <div class="live-preview-fields flex-1 h-full overflow-scroll" :class="{ 'p-3 bg-grey-30': poppedOut }">
                                <portal-target name="live-preview-fields" />
                            </div>

                            <resizer
                                @resized="setEditorWidth"
                                @resize-start="editorResizing = true"
                                @resize-end="editorResizing = false"
                                @collapsed="collapseEditor"
                            />
                        </div>
                    </transition>

                    <transition name="live-preview-contents-slide">
                        <div v-show="panesVisible" class="live-preview-contents items-center justify-center overflow-auto" :class="{ 'pointer-events-none': editorResizing }">
                            <iframe ref="iframe" frameborder="0" :class="previewDevice" />
                        </div>
                    </transition>

                </div>

            </div>
        </portal>

    </div>

</template>

<script>
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
        amp: Boolean
    },

    data() {
        return {
            panesVisible: false,
            headerVisible: false,
            editorWidth: null,
            editorResizing: false,
            editorCollapsed: false,
            previewDevice: 'Responsive',
            previewAmp: false,
            previewDevices: ['Responsive', 'Laptop', 'Tablet', 'Mobile'],
            provides: {
                storeName: this.name
            },
            channel: null,
            poppedOut: false,
            popoutWindow: null,
            popoutResponded: false,
        }
    },

    computed: {

        payload() {
            return {
                amp: this.previewAmp,
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
        },

        poppedOut() {
            this.$wait(300).then(() => EQCSS.apply());
        }

    },

    created() {
        this.editorWidth = localStorage.getItem(widthLocalStorageKey) || 400

        this.$mousetrap.bindGlobal('meta+shift+p', () => {
            this.previewing ? this.close() : this.$emit('opened-via-keyboard');
        });
    },

    beforeDestroy() {
        this.closePopout();
    },

    destroyed() {
        this.$mousetrap.unbind('meta+shift+p');
    },

    methods: {

        update: _.debounce(function () {
            if (this.poppedOut) {
                this.sendPayloadToPoppedOutWindow();
                return;
            }

            if (source) source.cancel();
            source = this.$axios.CancelToken.source();

            this.$axios.post(this.url, this.payload, { cancelToken: source.token }).then(response => {
                this.updateIframeContents(response.data);
            }).catch(e => {
                if (this.$axios.isCancel(e)) return;
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
            if (this.poppedOut) this.closePopout();

            this.animateOut().then(() => this.$emit('closed'));
        },

        animateIn() {
            return this
                .$wait(100)
                .then(() => {
                    this.headerVisible = true;
                    return this.$wait(200);
                })
                .then(() => {
                    this.panesVisible = true;
                    return this.$wait(300);
                }).then(() => EQCSS.apply());
        },

        animateOut() {
            this.panesVisible = false;
            this.headerVisible = false;
            return this.$wait(300);
        },

        setEditorWidth(width) {
            this.editorCollapsed = false;
            this.editorWidth = width;
            localStorage.setItem(widthLocalStorageKey, width);
        },

        collapseEditor() {
            this.editorCollapsed = true;
            this.editorWidth = 16;
        },

        popout() {
            this.poppedOut = true;
            this.channel = this.channel || new BroadcastChannel('livepreview');
            this.channel.onmessage = e => {
                switch (e.data.event) {
                    case 'popout.opened':
                        this.listenForPopoutClose();
                        this.sendPayloadToPoppedOutWindow();
                        this.updateIframeContents('');
                        break;
                    case 'popout.closed':
                        this.poppedOut = false;
                        this.update();
                    case 'popout.pong':
                        this.popoutResponded = true;
                        break;
                    default:
                        break;
                }
            };
            this.popoutWindow = window.open(this.url, 'livepreview', 'width=700,height=900');
        },

        closePopout() {
            this.popoutWindow.close();
        },

        sendPayloadToPoppedOutWindow() {
            this.channel.postMessage({ event: 'updated', url: this.url, payload: this.payload })
        },

        listenForPopoutClose() {
            this.channel.postMessage({ event: 'ping' });
            setTimeout(() => {
                if (this.popoutResponded) {
                    this.listenForPopoutClose();
                } else {
                    this.poppedOut = false;
                    this.update();
                }

                this.popoutResponded = false;
            }, 200);
        }
    }

}
</script>
