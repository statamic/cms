/**
 * Merge aria-describedby id lists, dropping empties and duplicates.
 */
export function mergeAriaDescribedBy(...parts: any[]): string | undefined;
/**
 * Resolve a control's id and field a11y attrs from a parent ui/Field.
 *
 * Id resolution: explicit prop, then a claimed parent Field id (when claimable),
 * then a generated id. Only the first control claims an auto-generated Field id.
 *
 * `describedBy` and `invalid` are only applied when this control's resolved id
 * matches the Field's id — so composite fieldtypes (Table, List, etc.) don't
 * mark every nested control invalid.
 *
 * Returns `{ id, labelId, describedBy, invalid }`.
 */
export function useUiFieldId(id: any): {
    id: import("vue").ComputedRef<any>;
    labelId: import("vue").ComputedRef<any>;
    describedBy: import("vue").ComputedRef<string | undefined>;
    invalid: import("vue").ComputedRef<boolean>;
};
export const UI_FIELD_ID_KEY: "uiFieldId";
