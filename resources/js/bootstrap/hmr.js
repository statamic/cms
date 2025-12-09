/**
 * This is a stub for when HMR is not enabled in the Control Panel build.
 * It provides a warning in the console with some guidance rather than throwing an error.
 */

let logged = false;

function log() {
    if (logged) return;
    console.warn(
        "Vue HMR is not enabled in this Control Panel build.\n",
        'See https://v6.statamic.dev/addons/vite-tooling for more information.'
    );
    logged = true;
}

export default {
    createRecord: log,
    rerender: log,
    reload: log
}
