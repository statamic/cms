<script setup>
import { computed, nextTick, ref, watch, useTemplateRef, onBeforeUnmount, onUnmounted, onBeforeMount } from 'vue';
import Resizer from '@statamic/components/live-preview/Resizer.vue';
import { injectContainerContext } from '@statamic/components/ui/Publish/Container.vue';
import debounce from '@statamic/util/debounce.js';
import { Select, Button } from '@statamic/ui';
import axios from 'axios';
import wait from '@statamic/util/wait.js';
import { mapValues } from 'lodash-es';

const props = defineProps({
    enabled: {
        type: Boolean,
        default: false,
        required: true,
    },
    url: {
        type: String,
        required: false,
    },
    targets: {
        type: Array,
        default: () => [],
        required: true,
    },
});

const emit = defineEmits(['opened', 'closed']);

const { name, blueprint, values } = injectContainerContext();
const portalEnabled = ref(false);
const panesVisible = ref(false);
const headerVisible = ref(false);
const widthLocalStorageKey = 'statamic.live-preview.editor-width';
const editorWidth = ref(localStorage.getItem(widthLocalStorageKey) || 400);
const editorResizing = ref(false);
const editorCollapsed = ref(false);
const channel = ref(null);
const poppedOut = ref(false);
const popoutWindow = ref(null);
const popoutResponded = ref(false);
const loading = ref(true);
const extras = ref({});
const token = ref(null);
const target = ref(0);
const previousUrl = ref(null);
const iframeContentContainer = useTemplateRef('contents');
let source;

const livePreviewFieldsPortal = computed(() => {
    return `live-preview-fields-${name.value}`;
});

watch(
    () => props.enabled,
    (enabled, wasEnabled) => {
        if (wasEnabled && !enabled) {
            nextTick(() => (portalEnabled.value = false));
        } else {
            portalEnabled.value = enabled;
        }

        if (!enabled) return;

        update();
        animateIn();
    },
);

const tokenizedUrl = computed(() => {
    let url = props.url;

    url += (url.includes('?') ? '&' : '?') + `target=${target.value}`;

    if (token.value) url += `&token=${token.value}`;

    return url;
});

const payload = computed(() => ({
    blueprint: blueprint.value.handle,
    preview: values.value,
    extras: extras.value,
}));

watch(
    [payload, target],
    (payload) => {
        if (props.enabled) update();
    },
    { deep: true },
);

const update = debounce(() => {
    if (source) source.abort();
    source = new AbortController();

    loading.value = true;

    axios
        .post(tokenizedUrl.value, payload.value, { signal: source.signal })
        .then((response) => {
            token.value = response.data.token;
            const url = response.data.url;
            const tgt = props.targets[target.value];
            const payload = { token: token.value, reference: props.reference };
            poppedOut.value
                ? channel.value.postMessage({ event: 'updated', url, target: tgt, payload })
                : updateIframeContents(url, target, payload);
            loading.value = false;
        })
        .catch((e) => {
            if (e.code === 'ERR_CANCELED') return;
            throw e;
        });
}, 150);

// This was in a mixin. Probably should go into a composable.
const hasIframeSourceChanged = (existingSrc, newSrc) => {
    existingSrc = new URL(existingSrc);
    newSrc = new URL(newSrc);
    existingSrc.searchParams.delete('live-preview');
    newSrc.searchParams.delete('live-preview');

    return existingSrc.toString() !== newSrc.toString();
};

// This was in a mixin. Probably should go into a composable.
const postMessageToIframe = (container, url, payload) => {
    // If the target is a relative url, we'll get the origin from the current window.
    const targetOrigin = /^https?:\/\//.test(url) ? new URL(url)?.origin : window.origin;

    container.firstChild.contentWindow.postMessage(
        {
            name: 'statamic.preview.updated',
            url,
            ...payload,
        },
        targetOrigin,
    );
};

// This was in a mixin. Probably should go into a composable.
function updateIframeContents(url, target, payload) {
    const iframe = document.createElement('iframe');
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('src', url);
    iframe.setAttribute('id', 'live-preview-iframe');
    setIframeAttributes(iframe);

    const container = iframeContentContainer.value;
    let iframeUrl = new URL(url);
    let cleanUrl = iframeUrl.host + iframeUrl.pathname;

    // If there's no iframe yet, just append it.
    if (!container.firstChild) {
        container.appendChild(iframe);
        previousUrl.value = cleanUrl;
        return;
    }

    let shouldRefresh = target.refresh;

    if (hasIframeSourceChanged(container.firstChild.src, iframe.src)) {
        shouldRefresh = true;
    }

    if (!shouldRefresh) {
        postMessageToIframe(container, url, payload);
        return;
    }

    let isSameOrigin = url.startsWith('/') || iframeUrl.host === window.location.host;
    let preserveScroll = isSameOrigin && cleanUrl === previousUrl.value;

    let scroll = preserveScroll
        ? [container.firstChild.contentWindow.scrollX ?? 0, container.firstChild.contentWindow.scrollY ?? 0]
        : null;

    container.replaceChild(iframe, container.firstChild);

    if (preserveScroll) {
        let iframeContentWindow = iframe.contentWindow;
        const iframeScrollUpdate = (event) => {
            iframeContentWindow.scrollTo(...scroll);
        };

        iframeContentWindow.addEventListener('DOMContentLoaded', iframeScrollUpdate, true);
        iframeContentWindow.addEventListener('load', iframeScrollUpdate, true);
    }

    previousUrl.value = cleanUrl;
}

function setIframeAttributes(iframe) {
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('class', previewDevice.value ? 'device' : 'responsive');
    if (previewDevice.value) {
        iframe.setAttribute('style', `width: ${previewDeviceWidth.value}; height: ${previewDeviceHeight.value}`);
    } else {
        iframe.removeAttribute('style');
    }
}

function animateIn() {
    return wait(100)
        .then(() => {
            headerVisible.value = true;
            return wait(200);
        })
        .then(() => {
            panesVisible.value = true;
            return wait(300);
        })
        .then(() => {
            // Components that update position based on native
            // resize events (like Popovers) need to be informed thusly
            window.dispatchEvent(new Event('resize'));
        });
}

function animateOut() {
    panesVisible.value = false;
    headerVisible.value = false;
    return wait(300);
}

const canPopOut = computed(() => typeof BroadcastChannel === 'function');

function popout() {
    poppedOut.value = true;
    channel.value = channel.value || new BroadcastChannel('livepreview');
    channel.value.onmessage = (e) => {
        switch (e.data.event) {
            case 'popout.opened':
                listenForPopoutClose();
                update();
                break;
            case 'popout.closed':
                poppedOut.value = false;
                update();
            case 'popout.pong':
                popoutResponded.value = true;
                break;
            default:
                break;
        }
    };

    const width = iframeContentContainer.value.clientWidth;
    const height = iframeContentContainer.value.clientHeight;
    const left = screen.width - width;
    popoutWindow.value = window.open(props.url, 'livepreview', `width=${width},height=${height},top=0,left=${left}`);
}

function listenForPopoutClose() {
    channel.value.postMessage({ event: 'ping' });
    setTimeout(() => {
        if (popoutResponded.value) {
            listenForPopoutClose();
        } else {
            poppedOut.value = false;
            update();
        }

        popoutResponded.value = false;
    }, 500);
}

function closePopout() {
    if (poppedOut.value) popoutWindow.value.close();
}

const targetSelectOptions = computed(() =>
    Object.values(
        mapValues(props.targets, (target, key) => {
            return { value: key, label: __(target.label) };
        }),
    ),
);

const deviceSelectOptions = computed(() => {
    let options = Object.values(
        mapValues(Statamic.$config.get('livePreview.devices'), (dimensions, device) => {
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
});

const previewDevice = ref(deviceSelectOptions.value[0].value);

watch(previewDevice, (device) => {
    setIframeAttributes(document.getElementById('live-preview-iframe'));
});

const previewDeviceWidth = computed(() => {
    console.log(previewDevice.value);
    if (previewDevice.value) {
        return `${Statamic.$config.get('livePreview.devices')[previewDevice.value].width}px`;
    }
});

const previewDeviceHeight = computed(() => {
    if (previewDevice) {
        return `${Statamic.$config.get('livePreview.devices')[previewDevice.value].height}px`;
    }
});

const inputs = computed(() => Statamic.$config.get('livePreview.inputs', {}));

function collapseEditor() {
    editorCollapsed.value = true;
    editorWidth.value = 16;
}

function setEditorWidth(width) {
    editorCollapsed.value = false;
    editorWidth.value = width;
    localStorage.setItem(widthLocalStorageKey, width);
}

function close() {
    if (poppedOut.value) closePopout();

    animateOut().then(() => emit('closed'));
}

const keybinding = ref(
    Statamic.$keys.bindGlobal('mod+shift+p', () => {
        props.enabled ? close() : emit('opened');
    }),
);

onUnmounted(() => keybinding.value.destroy());

Statamic.$events.$on(`live-preview.${name.value}.refresh`, () => {
    if (props.enabled) update();
});
</script>

<template>
    <v-portal :to="livePreviewFieldsPortal" :disabled="!portalEnabled">
        <slot name="default" />
    </v-portal>

    <portal v-if="enabled" name="live-preview" target-class="live-preview-portal">
        <div class="live-preview fixed flex flex-col">
            <transition name="live-preview-header-slide">
                <div v-show="headerVisible" class="live-preview-header">
                    <div class="dark:text-dark-150 text-base font-medium text-gray-700 ltr:mr-4 rtl:ml-4">
                        {{ __('Live Preview') }}
                    </div>
                    <div class="flex items-center gap-x-2">
                        <Button v-if="canPopOut && !poppedOut" size="sm" icon="maximize" @click="popout">
                            {{ __('Pop out') }}
                        </Button>
                        <Button v-if="poppedOut" size="sm" icon="minimize" @click="closePopout">{{
                            __('Pop in')
                        }}</Button>
                        <Select :options="deviceSelectOptions" v-model="previewDevice" v-show="!poppedOut" size="sm" />
                        <Select :options="targetSelectOptions" v-model="target" v-if="targets.length > 1" size="sm" />

                        <component
                            v-for="(component, handle) in inputs"
                            :key="handle"
                            :is="component"
                            :value="extras[handle]"
                            :loading="loading"
                            @input="componentUpdated(handle, $event)"
                        />

                        <slot name="buttons" />

                        <Button @click="close" icon="x" icon-only size="sm" variant="ghost" />
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
</template>
