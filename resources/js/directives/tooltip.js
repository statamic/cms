import { useTooltip } from '@/composables/tooltip.js';

const { show, hide, dismissFor } = useTooltip();

function getOptions(binding) {
    const value = binding.value;

    if (value === null || value === undefined || value === false || value === '') {
        return null;
    }

    return value;
}

function showsOnHover(options) {
    if (!options || typeof options === 'string') return true;

    return !options.focusOnly;
}

function showsOnFocus(options) {
    return !!options;
}

function handleShow(el, binding) {
    const options = getOptions(binding);
    if (options) {
        show(el, options);
    }
}

export default {
    mounted(el, binding) {
        el._tooltipBinding = binding;
        el._tooltipMouseEnter = () => {
            if (!showsOnHover(getOptions(el._tooltipBinding))) return;
            handleShow(el, el._tooltipBinding);
        };
        el._tooltipMouseLeave = () => {
            if (!showsOnHover(getOptions(el._tooltipBinding))) return;
            hide();
        };
        el._tooltipFocus = () => {
            if (!showsOnFocus(getOptions(el._tooltipBinding))) return;
            handleShow(el, el._tooltipBinding);
        };
        el._tooltipBlur = (event) => dismissFor(el, event);

        el.addEventListener('mouseenter', el._tooltipMouseEnter);
        el.addEventListener('mouseleave', el._tooltipMouseLeave);
        el.addEventListener('focus', el._tooltipFocus);
        el.addEventListener('blur', el._tooltipBlur);
    },

    updated(el, binding) {
        el._tooltipBinding = binding;
    },

    beforeUnmount(el) {
        el.removeEventListener('mouseenter', el._tooltipMouseEnter);
        el.removeEventListener('mouseleave', el._tooltipMouseLeave);
        el.removeEventListener('focus', el._tooltipFocus);
        el.removeEventListener('blur', el._tooltipBlur);
        dismissFor(el);
    },
};
