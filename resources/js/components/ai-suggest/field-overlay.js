// Prototype: helpers for laying text over a field, as its suggestions' preview (see AiSuggest.vue and AiSuggestLarge.vue) and the AI chat's
// changes (see AiChangeControls.vue) do.

// The field's own text box, in the wrapper round its fieldtype. Not an overlay laid over it, which is hidden from screen readers.
export const textBoxIn = (wrapper) => wrapper?.querySelector('input:not([aria-hidden]), textarea:not([aria-hidden])') ?? null;

// Bard's editor, in the wrapper round its fieldtype. Not a preview standing in its place, which takes its classes.
export const editorIn = (wrapper) => wrapper?.querySelector('.bard-editor .ProseMirror:not([data-ai-preview], [data-ai-preview] *)') ?? null;

// The editor's classes, for a preview to take its type and spacing, less the one for focus
export const proseClassOf = (editor) => [...editor.classList].filter((name) => name !== 'ProseMirror-focused').join(' ');

// A text box's padding, type and border, so text laid over it sits exactly where the box's own does
export function textStyleOf(box) {
    const css = getComputedStyle(box);

    return {
        padding: css.padding,
        // Longhands rather than the font shorthand, which the browser reports as empty when the field sets font features
        fontFamily: css.fontFamily,
        fontSize: css.fontSize,
        fontWeight: css.fontWeight,
        letterSpacing: css.letterSpacing,
        fontFeatureSettings: css.fontFeatureSettings,
        fontVariationSettings: css.fontVariationSettings,
        lineHeight: css.lineHeight,
        color: css.color,
        borderWidth: css.borderWidth,
        borderRadius: css.borderRadius,
    };
}
