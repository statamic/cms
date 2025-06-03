import PreviewHtml from './PreviewHtml';

export default {
    computed: {
        previewText() {
            return Object.entries(this.previews)
                .filter(([handle, value]) => {
                    if (!handle.endsWith('_')) return false;
                    handle = handle.substr(0, handle.length - 1); // Remove the trailing underscore.
                    const config = this.config.fields.find((f) => f.handle === handle) || {};
                    return config.replicator_preview === undefined ? this.showFieldPreviews : config.replicator_preview;
                })
                .map(([handle, value]) => value)
                .filter((value) => (['null', '[]', '{}', ''].includes(JSON.stringify(value)) ? null : value))
                .map((value) => {
                    if (value instanceof PreviewHtml) return value.html;

                    if (typeof value === 'string') return escapeHtml(value);

                    if (Array.isArray(value) && typeof value[0] === 'string') {
                        return escapeHtml(value.join(', '));
                    }

                    return escapeHtml(JSON.stringify(value));
                })
                .join(' / ');
        },
    },
};
