import { n as __exportAll } from "./rolldown-runtime-SbdDWaPq.js";
import { A as inject, B as openBlock, F as onBeforeUnmount, G as renderSlot, L as onMounted, _ as createBlock, _t as ref, g as createBaseVNode, p as Teleport } from "./vue.esm-bundler-DZ9hA_XK.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
// resources/js/pages/layout/LayoutPanel.vue
var LayoutPanel_exports = /* @__PURE__ */ __exportAll({ default: () => LayoutPanel_default });
var _sfc_main = {
	__name: "LayoutPanel",
	props: {
		side: {
			type: String,
			required: true,
			validator: (value) => ["left", "right"].includes(value)
		},
		mobileOpen: {
			type: Boolean,
			default: false
		}
	},
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const teleportTarget = props.side === "left" ? "#left-panel" : "#right-panel";
		const panelActive = inject(props.side === "left" ? "leftPanelActive" : "rightPanelActive");
		const navWasOriginallyOpen = ref(false);
		onMounted(() => {
			panelActive.value = true;
			if (props.side === "left") {
				navWasOriginallyOpen.value = localStorage.getItem("statamic.nav") !== "closed";
				Statamic.$events.$emit("nav.close", { persist: false });
			}
		});
		onBeforeUnmount(() => {
			panelActive.value = false;
			if (props.side === "left" && navWasOriginallyOpen.value) Statamic.$events.$emit("nav.open", { persist: false });
		});
		const __returned__ = {
			props,
			teleportTarget,
			panelActive,
			navWasOriginallyOpen,
			inject,
			onBeforeUnmount,
			onMounted,
			ref
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = ["data-mobile-open"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createBlock(Teleport, {
		defer: "",
		to: $setup.teleportTarget
	}, [createBaseVNode("div", {
		class: "layout-panel-content",
		"data-mobile-open": $props.mobileOpen
	}, [renderSlot(_ctx.$slots, "default")], 8, _hoisted_1)], 8, ["to"]);
}
var LayoutPanel_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "LayoutPanel.vue"]]);
export { LayoutPanel_exports as n, LayoutPanel_default as t };
