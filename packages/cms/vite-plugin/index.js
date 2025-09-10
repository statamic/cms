import vue from '@vitejs/plugin-vue';
import externals from './externals.js';
import tailwindExclusions from './tailwind-exclusions.js';
import preventServer from './prevent-server.js';

export default function (options = {}) {
    return [
        preventServer(),
        externals(),
        tailwindExclusions(),
        vue(options.vue || {}),
    ];
}
