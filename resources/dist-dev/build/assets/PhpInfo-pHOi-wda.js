import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, W as renderList, _ as createBlock, f as Fragment, g as createBaseVNode, it as withCtx, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Hn as Panel_default, Ut as Header_default, ft as Cell_default, lt as Row_default, pt as Table_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/utilities/PhpInfo.vue
var _sfc_main = {
	__name: "PhpInfo",
	props: ["phpinfo", "version"],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			props: __props,
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get CardPanel() {
				return Panel_default;
			},
			get Table() {
				return Table_default;
			},
			get TableRow() {
				return Row_default;
			},
			get TableCell() {
				return Cell_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = {
	class: "max-w-5xl 3xl:max-w-6xl mx-auto",
	"data-max-width-wrapper": ""
};
var _hoisted_2 = { class: "space-y-6" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [
		createVNode($setup["Head"], { title: [_ctx.__("PHP Info"), _ctx.__("Utilities")] }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			title: _ctx.__("PHP Info"),
			icon: "info"
		}, null, 8, ["title"]),
		createBaseVNode("section", _hoisted_2, [createVNode($setup["CardPanel"], { heading: _ctx.__("PHP Version") }, {
			default: withCtx(() => [createVNode($setup["Table"], null, {
				default: withCtx(() => [createVNode($setup["TableRow"], null, {
					default: withCtx(() => [createVNode($setup["TableCell"], { width: "30%" }, {
						default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("PHP Version")), 1)]),
						_: 1
					}), createVNode($setup["TableCell"], null, {
						default: withCtx(() => [createTextVNode(toDisplayString($props.version), 1)]),
						_: 1
					})]),
					_: 1
				})]),
				_: 1
			})]),
			_: 1
		}, 8, ["heading"]), (openBlock(true), createElementBlock(Fragment, null, renderList($props.phpinfo, (items, section) => {
			return openBlock(), createBlock($setup["CardPanel"], {
				key: section,
				heading: section
			}, {
				default: withCtx(() => [createVNode($setup["Table"], null, {
					default: withCtx(() => [(openBlock(true), createElementBlock(Fragment, null, renderList(items, (value, name) => {
						return openBlock(), createBlock($setup["TableRow"], { key: name }, {
							default: withCtx(() => [createVNode($setup["TableCell"], { width: "30%" }, {
								default: withCtx(() => [createTextVNode(toDisplayString(name), 1)]),
								_: 2
							}, 1024), createVNode($setup["TableCell"], null, {
								default: withCtx(() => [createTextVNode(toDisplayString(Array.isArray(value) ? value.join(", ") : value), 1)]),
								_: 2
							}, 1024)]),
							_: 2
						}, 1024);
					}), 128))]),
					_: 2
				}, 1024)]),
				_: 2
			}, 1032, ["heading"]);
		}), 128))])
	]);
}
var PhpInfo_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "PhpInfo.vue"]]);
//#endregion
export { PhpInfo_default as default };
