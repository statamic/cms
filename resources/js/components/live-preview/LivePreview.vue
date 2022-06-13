<template>

    <div>
        <portal :to="livePreviewFieldsPortal" :disabled="!previewing">
            <provider :variables="provides">
                <slot name="default" />
            </provider>
        </portal>

        <portal v-if="previewing" to="live-preview">
            <div class="live-preview fixed flex flex-col">

                <transition name="live-preview-header-slide">
                    <div v-show="headerVisible" class="live-preview-header">
                        <div class="text-base text-grey-70 font-medium mr-2">{{ __('Live Preview') }}</div>
                        <div class="flex items-center">
                            <label v-if="amp" class="mr-2"><input type="checkbox" v-model="previewAmp" /> AMP</label>
                            <button v-if="canPopOut && !poppedOut" class="btn" @click="popout">{{ __('Pop out') }}</button>
                            <button v-if="poppedOut" class="btn" @click="closePopout">{{ __('Pop in') }}</button>
                            <select-input :options="deviceSelectOptions" v-model="previewDevice" v-show="!poppedOut" class="ml-2" />

                            <component
                                v-for="(component, handle) in inputs"
                                :key="handle"
                                :is="component"
                                :value="extras[handle]"
                                :loading="loading"
                                @input="componentUpdated(handle, $event)"
                                class="ml-2" />

                            <slot name="buttons" />

                            <button
                                type="button"
                                class="btn-close"
                                @click="close"
                                v-html="'&times'" />
                        </div>
                    </div>
                </transition>

                <div class="live-preview-main">

                    <transition name="live-preview-editor-slide">
                        <div v-show="panesVisible" class="live-preview-editor" :style="{ width: poppedOut ? '100%' : `${editorWidth}px` }">
                            <div class="live-preview-fields flex-1 h-full overflow-scroll" :class="{
                                'p-3 bg-grey-30': poppedOut,
                                'live-preview-fields-wide': editorWidth >= 920,
                                'live-preview-fields-narrow': editorWidth < 920
                            }">
                                <portal-target :name="livePreviewFieldsPortal" />
                            </div>

                            <resizer
                                v-show="!poppedOut"
                                @resized="setEditorWidth"
                                @resize-start="editorResizing = true"
                                @resize-end="editorResizing = false"
                                @collapsed="collapseEditor"
                            />
                        </div>
                    </transition>

                    <transition name="live-preview-contents-slide">
                        <div v-show="panesVisible" ref="contents" class="live-preview-contents items-center justify-center overflow-auto" :class="{ 'pointer-events-none': editorResizing }">
                            <iframe ref="iframe" frameborder="0" :class="previewDevice ? 'device' : 'responsive'" :style="{ width: previewDeviceWidth, height: previewDeviceHeight }" />
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
            previewDevice: null,
            previewAmp: false,
            provides: {
                storeName: this.name
            },
            channel: null,
            poppedOut: false,
            popoutWindow: null,
            popoutResponded: false,
            loading: true,
            extras: {},
            keybinding: null,
        }
    },

    computed: {

        payload() {
            return {
                amp: this.previewAmp,
                blueprint: this.blueprint,
                preview: this.values,
                extras: this.extras
            }
        },

        deviceSelectOptions() {
            let options = Object.values(_.mapObject(this.$config.get('livePreview.devices'), (dimensions, device) => {
                return { value: device, label: __(device) };
            }));
            options.unshift({ value: null, label: __('Responsive') });
            return options;
        },

        previewDeviceWidth() {
            if (this.previewDevice) {
                return `${this.$config.get('livePreview.devices')[this.previewDevice].width}px`;
            }
        },

        previewDeviceHeight() {
            if (this.previewDevice) {
                return `${this.$config.get('livePreview.devices')[this.previewDevice].height}px`;
            }
        },

        inputs() {
            return this.$config.get('livePreview.inputs', {});
        },

        livePreviewFieldsPortal() {
            return `live-preview-fields-${this.storeName}`;
        },

        canPopOut() {
            return typeof BroadcastChannel === 'function';
        }

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

        this.keybinding = this.$keys.bindGlobal('mod+shift+p', () => {
            this.previewing ? this.close() : this.$emit('opened-via-keyboard');
        });
    },

    beforeDestroy() {
        this.closePopout();
    },

    destroyed() {
        this.keybinding.destroy();
    },

    methods: {

        update: _.debounce(function () {
            if (this.poppedOut) {
                this.sendPayloadToPoppedOutWindow();
                return;
            }

            if (source) source.cancel();
            source = this.$axios.CancelToken.source();

            this.loading = true;

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
            this.loading = false;
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
                })
                .then(() => {
                    // Components that update position based on native
                    // resize events (like Popovers) need to be informed thusly
                    window.dispatchEvent(new Event('resize'));
                });
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
                    case 'popout.loading':
                        this.loading = true;
                        break;
                    case 'popout.loaded':
                        this.loading = false;
                        break;
                    default:
                        break;
                }
            };

            const width = this.$refs.contents.clientWidth;
            const height = this.$refs.contents.clientHeight;
            const left = screen.width - width;
            this.popoutWindow = window.open(this.url, 'livepreview', `width=${width},height=${height},top=0,left=${left}`);
        },

        closePopout() {
            if (this.poppedOut) this.popoutWindow.close();
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
            }, 500);
        },

        componentUpdated(handle, value) {
            Vue.set(this.extras, handle, value);
        }
    }

}
</script>
