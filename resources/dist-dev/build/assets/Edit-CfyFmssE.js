import { B as openBlock, C as createVNode, f as Fragment, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { M as PublishForm_default, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/entries/Edit.vue
var _sfc_main = {
	__name: "Edit",
	props: [
		"actions",
		"collection",
		"title",
		"reference",
		"blueprint",
		"values",
		"extraValues",
		"localizedFields",
		"meta",
		"permalink",
		"originBehavior",
		"localizations",
		"hasOrigin",
		"originValues",
		"originMeta",
		"locale",
		"hasWorkingCopy",
		"revisionsEnabled",
		"readOnly",
		"canEditBlueprint",
		"canManagePublishState",
		"createAnotherUrl",
		"initialListingUrl",
		"previewTargets",
		"autosaveInterval",
		"itemActions",
		"itemActionUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			EntryPublishForm: PublishForm_default,
			Head: Head_default
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: $props.title }, null, 8, ["title"]), createVNode($setup["EntryPublishForm"], {
		"publish-container": "base",
		"initial-actions": $props.actions,
		method: "patch",
		"collection-handle": $props.collection,
		"initial-title": $props.title,
		"initial-reference": $props.reference,
		"initial-fieldset": $props.blueprint,
		"initial-values": $props.values,
		"initial-extra-values": $props.extraValues,
		"initial-localized-fields": $props.localizedFields,
		"initial-meta": $props.meta,
		"initial-permalink": $props.permalink,
		"origin-behavior": $props.originBehavior,
		"initial-localizations": $props.localizations,
		"initial-has-origin": $props.hasOrigin,
		"initial-origin-values": $props.originValues,
		"initial-origin-meta": $props.originMeta,
		"initial-site": $props.locale,
		"initial-is-working-copy": $props.hasWorkingCopy,
		"revisions-enabled": $props.revisionsEnabled,
		"initial-read-only": $props.readOnly,
		"can-edit-blueprint": $props.canEditBlueprint,
		"can-manage-publish-state": $props.canManagePublishState,
		"create-another-url": $props.createAnotherUrl,
		"initial-listing-url": $props.initialListingUrl,
		"preview-targets": $props.previewTargets,
		"autosave-interval": $props.autosaveInterval,
		"initial-item-actions": $props.itemActions,
		"item-action-url": $props.itemActionUrl
	}, null, 8, [
		"initial-actions",
		"collection-handle",
		"initial-title",
		"initial-reference",
		"initial-fieldset",
		"initial-values",
		"initial-extra-values",
		"initial-localized-fields",
		"initial-meta",
		"initial-permalink",
		"origin-behavior",
		"initial-localizations",
		"initial-has-origin",
		"initial-origin-values",
		"initial-origin-meta",
		"initial-site",
		"initial-is-working-copy",
		"revisions-enabled",
		"initial-read-only",
		"can-edit-blueprint",
		"can-manage-publish-state",
		"create-another-url",
		"initial-listing-url",
		"preview-targets",
		"autosave-interval",
		"initial-item-actions",
		"item-action-url"
	])], 64);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
