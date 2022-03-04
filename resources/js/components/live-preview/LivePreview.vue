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
                            <button v-if="canPopOut && !poppedOut" class="btn" @click="popout">{{ __('Pop out') }}</button>
                            <button v-if="poppedOut" class="btn" @click="closePopout">{{ __('Pop in') }}</button>
                            <select-input :options="deviceSelectOptions" v-model="previewDevice" v-show="!poppedOut" class="ml-2" />
                            <select-input :options="targetSelectOptions" v-model="target" class="ml-2" v-if="targets.length > 1" />

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
                        <div v-show="panesVisible" ref="contents" class="live-preview-contents items-center justify-center overflow-auto" :class="{ 'pointer-events-none': editorResizing }" />
                    </transition>

                </div>

            </div>
        </portal>

    </div>

</template>

<script>
import Provider from './Provider.vue';
import Resizer from './Resizer.vue';
import UpdatesIframe from './UpdatesIframe';

let source;
const widthLocalStorageKey = 'statamic.live-preview.editor-width';

export default {

    mixins: [
        UpdatesIframe
    ],

    components: {
        Provider,
        Resizer
    },

    props: {
        url: String,
        previewing: Boolean,
        targets: Array,
        values: Object,
        name: String,
        blueprint: String,
    },

    data() {
        return {
            panesVisible: false,
            headerVisible: false,
            editorWidth: null,
            editorResizing: false,
            editorCollapsed: false,
            previewDevice: null,
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
            token: null,
            target: 0,
        }
    },

    computed: {

        payload() {
            return {
                blueprint: this.blueprint,
                preview: this.values,
                extras: this.extras
            }
        },

        targetSelectOptions() {
            return Object.values(_.mapObject(this.targets, (target, key) => {
                return { value: key, label: __(target.label) };
            }));
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
        },

        tokenizedUrl() {
            let url = this.url;

            url += (url.includes('?') ? '&' : '?') + `target=${this.target}`;

            if (this.token) url += `&token=${this.token}`;

            return url;
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
        },

        target() {
            this.update();
        },

        previewDevice() {
            this.setIframeAttributes(document.getElementById('live-preview-iframe'));
        },

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
            if (source) source.cancel();
            source = this.$axios.CancelToken.source();

            this.loading = true;

            this.$axios.post(this.tokenizedUrl, this.payload, { cancelToken: source.token }).then(response => {
                this.token = response.data.token;
                const url = response.data.url;
                this.poppedOut
                    ? this.channel.postMessage({ event: 'updated', url })
                    : this.updateIframeContents(url);
                this.loading = false;
            }).catch(e => {
                if (this.$axios.isCancel(e)) return;
                throw e;
            });
        }, 150),

        setIframeAttributes(iframe) {
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('class', this.previewDevice ? 'device' : 'responsive');
            if (this.previewDevice) iframe.setAttribute('style', `width: ${this.previewDeviceWidth}; height: ${this.previewDeviceHeight}`);
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
                        this.update();
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

            const width = this.$refs.contents.clientWidth;
            const height = this.$refs.contents.clientHeight;
            const left = screen.width - width;
            this.popoutWindow = window.open(this.url, 'livepreview', `width=${width},height=${height},top=0,left=${left}`);
        },

        closePopout() {
            if (this.poppedOut) this.popoutWindow.close();
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
