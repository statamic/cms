import { ref, shallowRef, readonly } from 'vue';

const isVisible = ref(false);
const content = ref('');
const html = ref(false);
const targetEl = shallowRef(null);

let hideTimeout = null;
let showTimeout = null;

function setContent(el, options) {
    targetEl.value = el;

    if (typeof options === 'string') {
        content.value = options;
        html.value = false;
    } else if (options && typeof options === 'object') {
        content.value = options.content || '';
        html.value = options.html || false;
    } else {
        content.value = '';
        html.value = false;
    }
}

function show(el, options) {
    if (hideTimeout) {
        clearTimeout(hideTimeout);
        hideTimeout = null;
    }

    if (showTimeout) {
        clearTimeout(showTimeout);
    }

    // If already visible, update immediately (for moving between adjacent elements)
    if (isVisible.value) {
        setContent(el, options);
        return;
    }

    showTimeout = setTimeout(() => {
        setContent(el, options);

        if (content.value) {
            isVisible.value = true;
        }
    }, 200);
}

function hide() {
    if (showTimeout) {
        clearTimeout(showTimeout);
        showTimeout = null;
    }

    hideTimeout = setTimeout(() => {
        isVisible.value = false;
        targetEl.value = null;
        content.value = '';
        html.value = false;
    }, 50);
}

export function useTooltip() {
    return {
        isVisible: readonly(isVisible),
        content: readonly(content),
        html: readonly(html),
        targetEl: readonly(targetEl),
        show,
        hide,
    };
}
