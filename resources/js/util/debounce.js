export default function (func, wait, immediate) {
    let timeout;

    const debounced = function () {
        const context = this,
            args = arguments;

        clearTimeout(timeout);

        if (immediate && !timeout) func.apply(context, args);

        timeout = setTimeout(function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        }, wait);
    };

    debounced.cancel = function () {
        clearTimeout(timeout);
        timeout = null;
    };

    return debounced;
}
