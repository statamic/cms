import PreviewHtml from '@/components/fieldtypes/replicator/PreviewHtml.js';
import formatPreviewValue from '@/util/formatPreviewValue';
import { escapeHtml } from '@/bootstrap/globals.js';

/**
 * Build preview text from field values and mounted component previews.
 *
 * @param {Object} params - Parameters
 * @param {Object} params.previews - Preview data from mounted fieldtype components
 * @param {Object} params.config - Field configuration with fields array
 * @param {Object} params.values - Current field values
 * @param {boolean} params.showFieldPreviews - Whether to show field previews by default
 * @param {string} params.separator - Separator string to use between preview values
 * @returns {string} - The formatted preview text
 */
export function buildPreviewText({
    previews,
    config,
    values,
    showFieldPreviews,
    separator,
}) {
    const hasMountedPreviews = Object.keys(previews).length > 0;

    let previewValues;

    if (hasMountedPreviews) {
        // Use previews from mounted fieldtype components
        previewValues = Object.entries(previews)
            .filter(([handle, value]) => {
                if (!handle.endsWith('_')) return false;
                handle = handle.slice(0, -1); // Remove the trailing underscore
                const fields = Array.isArray(config.fields) ? config.fields : Object.values(config.fields || {});
                const fieldConfig = fields.find((f) => f.handle === handle);
                if (!fieldConfig) return false;
                return fieldConfig.replicator_preview === undefined ? showFieldPreviews : fieldConfig.replicator_preview;
            })
            .map(([handle, value]) => value)
            .filter((value) => {
                if (value == null || value === '') return false;
                if (typeof value === 'object' && !(value instanceof PreviewHtml) && !Array.isArray(value)) {
                    return false;
                }
                return true;
            })
            .map((value) => {
                if (value instanceof PreviewHtml) return value.html;
                if (typeof value === 'string') return escapeHtml(value);
                if (Array.isArray(value)) return escapeHtml(value.join(', '));
                return escapeHtml(String(value));
            })
            .filter((html) => html && html.trim() !== '');
    } else {
        // Fallback: extract values directly from values
        const fields = Array.isArray(config.fields) ? config.fields : Object.values(config.fields || {});
        previewValues = fields
            .filter((field) => {
                const shouldShow = field.replicator_preview === undefined ? showFieldPreviews : field.replicator_preview;
                if (!shouldShow) return false;
                const value = values?.[field.handle];
                if (value == null || value === '') return false;
                if (Array.isArray(value)) return value.length > 0;
                if (typeof value === 'object') return Object.keys(value).length > 0;
                return true;
            })
            .map((field) => formatPreviewValue(values?.[field.handle], field, { escape: true }))
            .filter((value) => value && value.trim() !== '');
    }

    return previewValues.join(separator);
}

export default buildPreviewText;
