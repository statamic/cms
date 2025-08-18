const StatamicCms = typeof window !== 'undefined' && window.StatamicCms ? window.StatamicCms : {};
const createProxy = () => new Proxy({}, { get: () => createProxy(), set: () => true, has: () => true });

// Individual named exports
export const Fieldtype = StatamicCms.Fieldtype || createProxy();
export const IndexFieldtype = StatamicCms.IndexFieldtype || createProxy();
export const FieldtypeMixin = StatamicCms.FieldtypeMixin || createProxy();
export const IndexFieldtypeMixin = StatamicCms.IndexFieldtypeMixin || createProxy();
export const DateFormatter = StatamicCms.DateFormatter || createProxy();
export const ItemActions = StatamicCms.ItemActions || createProxy();

// Namespace exports
export const ui = StatamicCms.ui || createProxy();
export const bard = StatamicCms.bard || createProxy();
export const savePipeline = StatamicCms.savePipeline || createProxy();

// Default export for compatibility
export default StatamicCms;
