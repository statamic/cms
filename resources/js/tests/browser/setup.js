import '../setup.js';

import { config as browserConfig } from 'vitest-browser-vue';

browserConfig.global.mocks = {
    __: (key) => key,
};

browserConfig.global.directives = {
    tooltip: () => {},
};

if (typeof window !== 'undefined') {
    window.__ = (key) => key;
}
