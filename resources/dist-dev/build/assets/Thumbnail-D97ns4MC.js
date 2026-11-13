import { U as openBlock, Y as resolveComponent, jt as normalizeClass, x as createElementBlock, y as createBlock } from "./vue.esm-bundler-T63OhLKP.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
// resources/js/components/assets/Browser/Thumbnail.vue
var _sfc_main = { props: {
	asset: Object,
	alt: {
		default: "",
		type: String
	},
	square: {
		default: false,
		type: Boolean
	}
} };
var _hoisted_1 = { class: "" };
var _hoisted_2 = ["src", "alt"];
var _hoisted_3 = ["src", "alt"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_file_icon = resolveComponent("file-icon");
	return openBlock(), createElementBlock("div", _hoisted_1, [$props.asset.thumbnail ? (openBlock(), createElementBlock("img", {
		key: 0,
		src: $props.asset.thumbnail,
		alt: $props.alt,
		class: normalizeClass(["asset-thumbnail mx-auto max-h-8 max-w-full rounded-sm", { "h-8 w-8 object-cover": $props.square }]),
		loading: "lazy",
		draggable: false,
		onError: _cache[0] || (_cache[0] = ($event) => $props.asset.thumbnail = null)
	}, null, 42, _hoisted_2)) : $props.asset.is_svg ? (openBlock(), createElementBlock("img", {
		key: 1,
		src: $props.asset.url,
		alt: $props.alt,
		class: "asset-thumbnail mx-auto h-8 max-w-full rounded-sm",
		loading: "lazy",
		draggable: false
	}, null, 8, _hoisted_3)) : (openBlock(), createBlock(_component_file_icon, {
		key: 2,
		extension: $props.asset.extension,
		class: "asset-thumbnail h-8 w-8 rounded-sm p-px"
	}, null, 8, ["extension"]))]);
}
var Thumbnail_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Thumbnail.vue"]]);
export { Thumbnail_default as t };
