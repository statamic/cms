import { B as openBlock, C as createVNode, f as Fragment, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router } from "./index.esm-B4SStoAz.js";
import { M as PublishForm_default, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/entries/Create.vue
var _sfc_main = {
	__name: "Create",
	props: [
		"actions",
		"collection",
		"collectionCreateLabel",
		"blueprint",
		"values",
		"extraValues",
		"meta",
		"localizations",
		"locale",
		"revisionsEnabled",
		"parent",
		"canEditBlueprint",
		"canManagePublishState",
		"createAnotherUrl",
		"initialListingUrl",
		"previewTargets"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		function saved(response) {
			router.get(response.data.data.edit_url + "?created=true");
		}
		const __returned__ = {
			saved,
			EntryPublishForm: PublishForm_default,
			get router() {
				return router;
			},
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
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: $props.collectionCreateLabel }, null, 8, ["title"]), createVNode($setup["EntryPublishForm"], {
		"is-creating": true,
		"publish-container": "base",
		"initial-actions": $props.actions,
		method: "post",
		"initial-title": $props.collectionCreateLabel,
		"collection-handle": $props.collection,
		"initial-fieldset": $props.blueprint,
		"initial-values": $props.values,
		"initial-extra-values": $props.extraValues,
		"initial-meta": $props.meta,
		"initial-localizations": $props.localizations,
		"initial-has-origin": false,
		"initial-origin-values": {},
		"revisions-enabled": $props.revisionsEnabled,
		"initial-site": $props.locale,
		parent: $props.parent,
		"can-manage-publish-state": $props.canManagePublishState,
		"can-edit-blueprint": $props.canEditBlueprint,
		"create-another-url": $props.createAnotherUrl,
		"initial-listing-url": $props.initialListingUrl,
		"preview-targets": $props.previewTargets,
		onSaved: $setup.saved
	}, null, 8, [
		"initial-actions",
		"initial-title",
		"collection-handle",
		"initial-fieldset",
		"initial-values",
		"initial-extra-values",
		"initial-meta",
		"initial-localizations",
		"revisions-enabled",
		"initial-site",
		"parent",
		"can-manage-publish-state",
		"can-edit-blueprint",
		"create-another-url",
		"initial-listing-url",
		"preview-targets"
	])], 64);
}
var Create_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Create.vue"]]);
//#endregion
export { Create_default as default };
