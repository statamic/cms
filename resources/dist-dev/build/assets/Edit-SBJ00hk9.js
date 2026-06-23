import { B as openBlock, C as createVNode, f as Fragment, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { j as PublishForm_default, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/terms/Edit.vue
var _sfc_main = {
	__name: "Edit",
	props: [
		"actions",
		"taxonomy",
		"title",
		"reference",
		"blueprint",
		"values",
		"meta",
		"localizedFields",
		"published",
		"locale",
		"localizations",
		"hasOrigin",
		"originValues",
		"originMeta",
		"permalink",
		"hasWorkingCopy",
		"revisionsEnabled",
		"readOnly",
		"canEditBlueprint",
		"createAnotherUrl",
		"listingUrl",
		"previewTargets",
		"itemActions",
		"hasTemplate"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			TermPublishForm: PublishForm_default,
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
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: $props.title }, null, 8, ["title"]), createVNode($setup["TermPublishForm"], {
		"publish-container": "base",
		"initial-actions": $props.actions,
		method: "patch",
		"taxonomy-handle": $props.taxonomy,
		"initial-title": $props.title,
		"initial-reference": $props.reference,
		"initial-fieldset": $props.blueprint,
		"initial-values": $props.values,
		"initial-meta": $props.meta,
		"initial-localized-fields": $props.localizedFields,
		"initial-published": $props.published,
		"initial-site": $props.locale,
		"initial-localizations": $props.localizations,
		"initial-has-origin": $props.hasOrigin,
		"initial-origin-values": $props.originValues,
		"initial-origin-meta": $props.originMeta,
		"initial-permalink": $props.permalink,
		"initial-is-working-copy": $props.hasWorkingCopy,
		"revisions-enabled": $props.revisionsEnabled,
		"initial-read-only": $props.readOnly,
		"can-edit-blueprint": $props.canEditBlueprint,
		"create-another-url": $props.createAnotherUrl,
		"listing-url": $props.listingUrl,
		"preview-targets": $props.previewTargets,
		"initial-item-actions": $props.itemActions,
		"has-template": $props.hasTemplate
	}, null, 8, [
		"initial-actions",
		"taxonomy-handle",
		"initial-title",
		"initial-reference",
		"initial-fieldset",
		"initial-values",
		"initial-meta",
		"initial-localized-fields",
		"initial-published",
		"initial-site",
		"initial-localizations",
		"initial-has-origin",
		"initial-origin-values",
		"initial-origin-meta",
		"initial-permalink",
		"initial-is-working-copy",
		"revisions-enabled",
		"initial-read-only",
		"can-edit-blueprint",
		"create-another-url",
		"listing-url",
		"preview-targets",
		"initial-item-actions",
		"has-template"
	])], 64);
}
var Edit_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Edit.vue"]]);
//#endregion
export { Edit_default as default };
