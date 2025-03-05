import PreviewHtml from './PreviewHtml';

export default {
    computed: {
        previewText() {
            const previews = this.previews.filter((value, handle) => {
                const config = this.config.fields.find((f) => f.handle === handle) || {};
                return config.replicator_preview === undefined ? this.showFieldPreviews : config.replicator_preview;
            });

            return Object.values(previews)
                .filter((value) => {
                    if (['null', '[]', '{}', ''].includes(JSON.stringify(value))) return null;
                    return value;
                })
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
