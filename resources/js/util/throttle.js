export default function (func, timeFrame) {
    let lastTime = 0;
    let timeoutId = null;

    return function (...args) {
        const now = Date.now();

        clearTimeout(timeoutId);

        if (now - lastTime >= timeFrame) {
            func(...args);
            lastTime = now;
        } else {
            timeoutId = setTimeout(() => {
                func(...args);
                lastTime = Date.now();
                timeoutId = null;
            }, timeFrame - (now - lastTime));
        }
    };
}
