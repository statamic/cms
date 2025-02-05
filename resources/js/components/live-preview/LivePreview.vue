<template>
    <div>
        <v-portal :to="livePreviewFieldsPortal" :disabled="!portalEnabled">
            <provider :variables="provides">
                <slot name="default" />
            </provider>
        </v-portal>

        <portal v-if="previewing" name="live-preview" target-class="live-preview-portal">
            <div class="live-preview fixed flex flex-col">
                <transition name="live-preview-header-slide">
                    <div v-show="headerVisible" class="live-preview-header">
                        <div class="text-base font-medium text-gray-700 dark:text-dark-150 ltr:mr-4 rtl:ml-4">
                            {{ __('Live Preview') }}
                        </div>
                        <div class="flex items-center">
                            <button v-if="canPopOut && !poppedOut" class="btn" @click="popout">
                                {{ __('Pop out') }}
                            </button>
                            <button v-if="poppedOut" class="btn" @click="closePopout">{{ __('Pop in') }}</button>
                            <select-input
                                :options="deviceSelectOptions"
                                v-model="previewDevice"
                                v-show="!poppedOut"
                                class="ltr:ml-4 rtl:mr-4"
                            />
                            <select-input
                                :options="targetSelectOptions"
                                v-model="target"
                                class="ltr:ml-4 rtl:mr-4"
                                v-if="targets.length > 1"
                            />

                            <component
                                v-for="(component, handle) in inputs"
                                :key="handle"
                                :is="component"
                                :value="extras[handle]"
                                :loading="loading"
                                @input="componentUpdated(handle, $event)"
                                class="ltr:ml-4 rtl:mr-4"
                            />

                            <slot name="buttons" />

                            <button type="button" class="btn-close" @click="close" v-html="'&times'" />
                        </div>
                    </div>
                </transition>

                <div class="live-preview-main">
                    <transition name="live-preview-editor-slide">
                        <div
                            v-show="panesVisible"
                            class="live-preview-editor @container/live-preview"
                            :style="{ width: poppedOut ? '100%' : `${editorWidth}px` }"
                        >
                            <div class="live-preview-fields h-full flex-1 overflow-scroll">
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
                        <div
                            v-show="panesVisible"
                            ref="contents"
                            class="live-preview-contents items-center justify-center overflow-auto"
                            :class="{ 'pointer-events-none': editorResizing }"
                        />
                    </transition>
                </div>
            </div>
        </portal>
    </div>
</template>

<script>
import Provider from '../portals/Provider.vue';
import Resizer from './Resizer.vue';
import UpdatesIframe from './UpdatesIframe';

let source;
const widthLocalStorageKey = 'statamic.live-preview.editor-width';

export default {
    mixins: [UpdatesIframe],

    components: {
        Provider,
        Resizer,
    },

    props: {
        url: String,
        previewing: Boolean,
        targets: Array,
        values: Object,
        name: String,
        blueprint: String,
        reference: String,
    },

    data() {
        return {
            portalEnabled: false,
            panesVisible: false,
            headerVisible: false,
            editorWidth: null,
            editorResizing: false,
            editorCollapsed: false,
            previewDevice: null,
            provides: {
                storeName: this.name,
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
            previousUrl: null,
        };
    },

    computed: {
        payload() {
            return {
                blueprint: this.blueprint,
                preview: this.values,
                extras: this.extras,
            };
        },

        targetSelectOptions() {
            return Object.values(
                _.mapObject(this.targets, (target, key) => {
                    return { value: key, label: __(target.label) };
                }),
            );
        },

        deviceSelectOptions() {
            let options = Object.values(
                _.mapObject(this.$config.get('livePreview.devices'), (dimensions, device) => {
                    if (device === 'Responsive') {
                        return { value: null, label: __('Responsive') };
                    }

                    return { value: device, label: __(device) };
                }),
            );

            if (options.filter((option) => option.label === __('Responsive')).length === 0) {
                options.unshift({ value: null, label: __('Responsive') });
            }

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
            return `live-preview-fields-${this.name}`;
        },

        canPopOut() {
            return typeof BroadcastChannel === 'function';
        },

        tokenizedUrl() {
            let url = this.url;

            url += (url.includes('?') ? '&' : '?') + `target=${this.target}`;

            if (this.token) url += `&token=${this.token}`;

            return url;
        },
    },

    watch: {
        previewing(enabled, wasEnabled) {
            if (wasEnabled && !enabled) {
                this.$nextTick(() => (this.portalEnabled = false));
            } else {
                this.portalEnabled = enabled;
            }

            if (!enabled) return;

            this.update();
            this.animateIn();
        },

        payload: {
            deep: true,
            handler(payload) {
                if (this.previewing) this.update();
            },
        },

        target() {
            this.update();
        },

        previewDevice() {
            this.setIframeAttributes(document.getElementById('live-preview-iframe'));
        },
    },

    created() {
        this.previewDevice = this.deviceSelectOptions[0].value;
        this.editorWidth = localStorage.getItem(widthLocalStorageKey) || 400;

        this.keybinding = this.$keys.bindGlobal('mod+shift+p', () => {
            this.previewing ? this.close() : this.$emit('opened-via-keyboard');
        });

        this.$events.$on(`live-preview.${this.name}.refresh`, () => {
            this.update();
        });
    },

    beforeUnmount() {
        this.closePopout();
    },

    unmounted() {
        this.keybinding.destroy();
    },

    methods: {
        update: _.debounce(function () {
            if (source) source.abort();
            source = new AbortController();

            this.loading = true;

            this.$axios
                .post(this.tokenizedUrl, this.payload, { signal: source.signal })
                .then((response) => {
                    this.token = response.data.token;
                    const url = response.data.url;
                    const target = this.targets[this.target];
                    const payload = { token: this.token, reference: this.reference };
                    this.poppedOut
                        ? this.channel.postMessage({ event: 'updated', url, target, payload })
                        : this.updateIframeContents(url, target, payload);
                    this.loading = false;
                })
                .catch((e) => {
                    if (e.code === 'ERR_CANCELED') return;
                    throw e;
                });
        }, 150),

        setIframeAttributes(iframe) {
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('class', this.previewDevice ? 'device' : 'responsive');
            if (this.previewDevice) {
                iframe.setAttribute('style', `width: ${this.previewDeviceWidth}; height: ${this.previewDeviceHeight}`);
            } else {
                iframe.removeAttribute('style');
            }
        },

        close() {
            if (this.poppedOut) this.closePopout();

            this.animateOut().then(() => this.$emit('closed'));
        },

        animateIn() {
            return this.$wait(100)
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
            this.channel.onmessage = (e) => {
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
            this.popoutWindow = window.open(
                this.url,
                'livepreview',
                `width=${width},height=${height},top=0,left=${left}`,
            );
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
            this.extras[handle] = value;
        },
    },
};
</script>
