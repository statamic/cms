import PreviewHtml from '@/components/fieldtypes/replicator/PreviewHtml.js';
import extractBardText from '@/util/extractBardText';
import { escapeHtml } from '@/bootstrap/globals.js';

/**
 * Normalize field options to a consistent format for lookup.
 * Handles array-of-strings, array-of-objects {value, label} or {key, value}, and plain objects.
 *
 * @param {Array|Object} options - The options configuration
 * @returns {Array} - Array of {value, label} objects
 */
function resolveOptions(options) {
    if (!options) return [];

    // Plain object: {key: value, key2: value2}
    if (!Array.isArray(options)) {
        return Object.entries(options).map(([key, val]) => ({
            value: key,
            label: val,
        }));
    }

    // Array of strings: ['option1', 'option2']
    if (options.length > 0 && typeof options[0] === 'string') {
        return options.map((opt) => ({
            value: opt,
            label: opt,
        }));
    }

    // Array of objects - normalize key/value to value/label
    return options.map((opt) => {
        if (typeof opt === 'object' && opt !== null) {
            return {
                value: opt.value !== undefined ? opt.value : opt.key,
                label: opt.label !== undefined ? opt.label : opt.value,
            };
        }
        return { value: opt, label: opt };
    });
}

/**
 * Resolve option label(s) for a given value.
 *
 * @param {*} value - The selected value(s)
 * @param {Object} fieldConfig - The field configuration
 * @returns {string|null} - The resolved label(s) or null
 */
function resolveOptionLabel(value, fieldConfig) {
    const options = resolveOptions(fieldConfig.options);
    if (options.length === 0) return null;

    const findLabel = (val) => {
        const option = options.find((opt) => opt.value === val);
        return option ? option.label : val;
    };

    if (Array.isArray(value)) {
        if (value.length === 0) return null;
        return value.map(findLabel).join(', ');
    }

    return findLabel(value);
}

/**
 * Truncate a string to a maximum length.
 *
 * @param {string} str - The string to truncate
 * @param {number} maxLength - Maximum length
 * @returns {string} - Truncated string
 */
function truncate(str, maxLength) {
    if (!str || str.length <= maxLength) return str;
    return str.slice(0, maxLength) + '...';
}

/**
 * Format a preview value for display in replicator sets.
 *
 * @param {*} value - The value to format
 * @param {Object} fieldConfig - The field configuration
 * @param {Object} options - Options for formatting
 * @param {boolean} options.escape - Whether to escape HTML in the output (default: false)
 * @returns {string|null} - The formatted preview value, or null if the value should be skipped
 */
export default function formatPreviewValue(value, fieldConfig, options = {}) {
    const { escape = false } = options;

    if (value == null || value === '') return null;

    // Handle PreviewHtml instances
    if (value instanceof PreviewHtml) {
        return value.html;
    }

    const type = fieldConfig?.type;

    // Type-specific handling (ordered before generic fallbacks)

    // Toggle: ✓ Field Label / ✗ Field Label
    if (type === 'toggle') {
        const display = fieldConfig.display || 'Toggle';
        const prefix = value ? '✓' : '✗';
        const result = display ? `${prefix} ${display}` : prefix;
        return escape ? escapeHtml(result) : result;
    }

    // Select, Radio, Button Group: resolved option label
    if (type === 'select' || type === 'radio' || type === 'button_group') {
        const label = resolveOptionLabel(value, fieldConfig);
        if (!label) return null;
        return escape ? escapeHtml(label) : label;
    }

    // Checkboxes: labels joined by ', '
    if (type === 'checkboxes') {
        const label = resolveOptionLabel(value, fieldConfig);
        if (!label) return null;
        return escape ? escapeHtml(label) : label;
    }

    // Dictionary: same as select (uses config.options with loaded meta)
    if (type === 'dictionary') {
        const label = resolveOptionLabel(value, fieldConfig);
        if (!label) return null;
        return escape ? escapeHtml(label) : label;
    }

    // Replicator: Display: N set(s)
    if (type === 'replicator') {
        const display = fieldConfig.display || 'Replicator';
        const count = Array.isArray(value) ? value.length : 0;
        const result = `${display}: ${count} ${count === 1 ? 'Set' : 'Sets'}`;
        return escape ? escapeHtml(result) : result;
    }

    // Grid: Display: N row(s)
    if (type === 'grid') {
        const display = fieldConfig.display || 'Grid';
        const count = Array.isArray(value) ? value.length : 0;
        const result = `${display}: ${count} ${count === 1 ? 'Row' : 'Rows'}`;
        return escape ? escapeHtml(result) : result;
    }

    // Assets: simplified checkmark
    if (type === 'assets') {
        const hasAssets = Array.isArray(value) && value.length > 0;
        const result = hasAssets ? '✓' : '✗';
        return escape ? escapeHtml(result) : result;
    }

    // Color: show hex string
    if (type === 'color') {
        const colorValue = typeof value === 'string' ? value : value?.hex || value?.color;
        if (!colorValue) return null;
        return escape ? escapeHtml(String(colorValue)) : String(colorValue);
    }

    // Code: truncate code content
    if (type === 'code') {
        const codeValue = typeof value === 'string' ? value : value?.code;
        if (!codeValue) return null;
        const truncated = truncate(codeValue, 60);
        return escape ? escapeHtml(truncated) : truncated;
    }

    // Table: joined cell values
    if (type === 'table') {
        if (!Array.isArray(value)) return null;
        const rows = value
            .map((row) => {
                if (!row || !Array.isArray(row.cells)) return '';
                return row.cells.filter(Boolean).join(', ');
            })
            .filter(Boolean);
        if (rows.length === 0) return null;
        const result = rows.join(', ');
        return escape ? escapeHtml(result) : result;
    }

    // Array: key: value pairs joined
    if (type === 'array') {
        if (typeof value !== 'object' || value === null || Array.isArray(value)) return null;
        const entries = Object.entries(value)
            .map(([k, v]) => `${k}: ${v}`)
            .join(', ');
        if (!entries) return null;
        return escape ? escapeHtml(entries) : entries;
    }

    // Entries / Terms / Users: count only
    if (type === 'entries' || type === 'terms' || type === 'users') {
        const count = Array.isArray(value) ? value.length : 0;
        const result = `${count} ${count === 1 ? 'Item' : 'Items'}`;
        return escape ? escapeHtml(result) : result;
    }

    // Link: show raw string value (usually a URL)
    if (type === 'link') {
        const linkValue = typeof value === 'string' ? value : value?.url || value?.permalink;
        if (!linkValue) return null;
        return escape ? escapeHtml(String(linkValue)) : String(linkValue);
    }

    // Revealer: always hidden
    if (type === 'revealer') {
        return null;
    }

    // Bard: Display: N block(s)
    if (type === 'bard' && Array.isArray(value)) {
        const display = fieldConfig.display || 'Content';
        const count = value.length;
        const result = `${display}: ${count} ${count === 1 ? 'Block' : 'Blocks'}`;
        return escape ? escapeHtml(result) : result;
    }

    // Markdown: pass through as-is (markdown is human-readable)
    if (type === 'markdown') {
        const mdValue = typeof value === 'string' ? value : null;
        if (!mdValue) return null;
        return escape ? escapeHtml(mdValue) : mdValue;
    }

    // Handle array of strings (e.g., select, tags) - fallback for non-typed arrays
    if (
        Array.isArray(value) &&
        value.length > 0 &&
        typeof value[0] === 'string'
    ) {
        const joined = value.join(', ');
        return escape ? escapeHtml(joined) : joined;
    }

    // Skip complex objects/arrays that would show as [object Object] or JSON
    if (
        Array.isArray(value) ||
        (typeof value === 'object' && !(value instanceof PreviewHtml))
    ) {
        return null;
    }

    const stringValue = String(value);
    return escape ? escapeHtml(stringValue) : stringValue;
}
