export default function (func, wait, immediate) {
    let timeout;

    return function () {
        const context = this,
            args = arguments;

        clearTimeout(timeout);

        if (immediate && !timeout) func.apply(context, args);

        timeout = setTimeout(function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        }, wait);
    };
}
