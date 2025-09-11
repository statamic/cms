import vue from '@vitejs/plugin-vue';
import externals from './externals.js';
import preventServer from './prevent-server.js';

export default function (options = {}) {
    return [
        preventServer(),
        externals(),
        vue(options.vue || {}),
    ];
}
