import { Pt as toDisplayString, T as createVNode, U as openBlock, ct as withCtx, p as Fragment, q as renderList, v as createBaseVNode, w as createTextVNode, x as createElementBlock, y as createBlock } from "./vue.esm-bundler-T63OhLKP.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
import { n as form_default } from "./index.esm-CUcrycdV.js";
import { Ht as Header_default, Qr as Button_default, Xn as Panel_default, ct as Row_default, dt as Cell_default, er as Card_default, ft as Table_default } from "./ui-d_k7wG4y.js";
import { r as Head_default } from "./index-DnBwcRtG.js";
// resources/js/pages/Duplicates.vue
var _sfc_main = {
	__name: "Duplicates",
	props: {
		duplicates: Object,
		regenerateUrl: String
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			get Form() {
				return form_default;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Panel() {
				return Panel_default;
			},
			get Card() {
				return Card_default;
			},
			get Table() {
				return Table_default;
			},
			get TableRow() {
				return Row_default;
			},
			get TableCell() {
				return Cell_default;
			},
			get Button() {
				return Button_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = ["textContent"];
var _hoisted_2 = ["value"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: _ctx.__("Duplicate IDs") }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			icon: "duplicate",
			title: _ctx.__("Duplicate IDs")
		}, null, 8, ["title"]),
		$props.duplicates.length === 0 ? (openBlock(), createElementBlock("div", {
			key: 0,
			class: "rounded-lg border border-dashed border-gray-300 p-6 text-center text-gray-500",
			textContent: toDisplayString(_ctx.__("No items with duplicate IDs."))
		}, null, 8, _hoisted_1)) : (openBlock(true), createElementBlock(Fragment, { key: 1 }, renderList($props.duplicates, (paths, id) => {
			return openBlock(), createBlock($setup["Panel"], { heading: id }, {
				default: withCtx(() => [createVNode($setup["Card"], { class: "py-0!" }, {
					default: withCtx(() => [createVNode($setup["Table"], null, {
						default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList(paths, (path) => {
							return openBlock(), createBlock($setup["TableRow"], null, {
								default: withCtx(() => [createVNode($setup["TableCell"], { class: "font-mono" }, {
									default: withCtx(() => [createTextVNode(toDisplayString(path), 1)]),
									_: 2
								}, 1024), createVNode($setup["TableCell"], { class: "flex items-center justify-end" }, {
									default: withCtx(() => [createVNode($setup["Form"], {
										method: "POST",
										action: $props.regenerateUrl
									}, {
										default: withCtx(() => [createBaseVNode("input", {
											type: "hidden",
											name: "path",
											value: path
										}, null, 8, _hoisted_2), createVNode($setup["Button"], {
											size: "sm",
											type: "submit"
										}, {
											default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Regenerate")), 1)]),
											_: 1
										})]),
										_: 2
									}, 1032, ["action"])]),
									_: 2
								}, 1024)]),
								_: 2
							}, 1024);
						}), 256))]),
						_: 2
					}, 1024)]),
					_: 2
				}, 1024)]),
				_: 2
			}, 1032, ["heading"]);
		}), 256))
	], 64);
}
var Duplicates_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Duplicates.vue"]]);
export { Duplicates_default as default };
