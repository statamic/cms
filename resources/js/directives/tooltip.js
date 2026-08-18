import { useTooltip } from '@/composables/tooltip.js';

const { show, hide, dismissFor } = useTooltip();

function getOptions(binding) {
    const value = binding.value;

    if (value === null || value === undefined || value === false || value === '') {
        return null;
    }

    return value;
}

function isNativelyFocusable(el) {
    const tag = el.tagName;

    return (
        ['A', 'BUTTON', 'INPUT', 'SELECT', 'TEXTAREA', 'SUMMARY'].includes(tag) ||
        el.isContentEditable
    );
}

function shouldBecomeFocusable(el) {
    if (el.hasAttribute('tabindex') || isNativelyFocusable(el) || el.closest('label')) {
        return false;
    }

    return true;
}

function handleMouseEnter(el, binding) {
    const options = getOptions(binding);
    if (options) {
        show(el, options);
    }
}

export default {
    mounted(el, binding) {
        el._tooltipBinding = binding;
        el._tooltipMouseEnter = () => handleMouseEnter(el, el._tooltipBinding);
        el._tooltipMouseLeave = hide;
        el._tooltipBlur = (event) => dismissFor(el, event);

        if (shouldBecomeFocusable(el)) {
            el.tabIndex = 0;
            el._tooltipAddedTabIndex = true;
        }

        el.addEventListener('mouseenter', el._tooltipMouseEnter);
        el.addEventListener('mouseleave', el._tooltipMouseLeave);
        el.addEventListener('focus', el._tooltipMouseEnter);
        el.addEventListener('blur', el._tooltipBlur);
    },

    updated(el, binding) {
        el._tooltipBinding = binding;
    },

    beforeUnmount(el) {
        el.removeEventListener('mouseenter', el._tooltipMouseEnter);
        el.removeEventListener('mouseleave', el._tooltipMouseLeave);
        el.removeEventListener('focus', el._tooltipMouseEnter);
        el.removeEventListener('blur', el._tooltipBlur);
        if (el._tooltipAddedTabIndex) {
            el.removeAttribute('tabindex');
            delete el._tooltipAddedTabIndex;
        }
        dismissFor(el);
    },
};
