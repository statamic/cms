import { n as __exportAll } from "./rolldown-runtime-SbdDWaPq.js";
import { B as onUnmounted, F as nextTick, H as openBlock, W as provide, l as vShow, m as Teleport, nt as watch, p as Fragment, q as renderSlot, st as withDirectives, tt as useTemplateRef, v as createBaseVNode, x as createElementBlock, y as createBlock, yt as ref, z as onMounted } from "./vue.esm-bundler-DDHDr1YM.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-DjkACiAr.js";
// resources/js/composables/use-resizable.js
function getStoredWidth(key) {
	if (!key) return null;
	const stored = localStorage.getItem(key);
	if (stored === null) return null;
	const width = parseInt(stored, 10);
	return Number.isFinite(width) ? width : null;
}
function setStoredWidth(key, width) {
	if (!key) return;
	localStorage.setItem(key, String(width));
}
function clearStoredWidth(key) {
	if (!key) return;
	localStorage.removeItem(key);
}
function useResizable() {
	const cleanupFns = [];
	function makeResizable(panelRef, activeRef, { edge = "right", minWidth = 200, maxWidth = 800, defaultWidth = null, storageKey = null } = {}) {
		let cleanup = null;
		watch(activeRef, (active) => {
			if (active) nextTick(() => {
				const panel = panelRef.value;
				if (!panel) return;
				const resolvedEdge = document.documentElement.dir === "rtl" ? edge === "right" ? "left" : "right" : edge;
				const resolveDefaultWidth = () => typeof defaultWidth === "function" ? defaultWidth() : defaultWidth;
				const clampWidth = (width) => Math.min(maxWidth, Math.max(minWidth, width));
				const applyWidth = (width) => {
					panel.style.width = typeof width === "string" ? width : `${clampWidth(width)}px`;
				};
				const initialWidth = getStoredWidth(storageKey) ?? resolveDefaultWidth();
				if (initialWidth !== null && initialWidth !== void 0) applyWidth(initialWidth);
				const handle = document.createElement("div");
				handle.dataset.resizeHandle = "";
				handle.style.position = "absolute";
				handle.style.top = "0";
				handle.style.bottom = "0";
				handle.style.width = "7px";
				handle.style.cursor = "col-resize";
				handle.style.zIndex = "var(--z-index-draggable)";
				handle.style[resolvedEdge] = "0px";
				panel.appendChild(handle);
				const resetToDefaultWidth = () => {
					clearStoredWidth(storageKey);
					const currentDefaultWidth = resolveDefaultWidth();
					if (currentDefaultWidth !== null && currentDefaultWidth !== void 0) {
						applyWidth(currentDefaultWidth);
						return;
					}
					panel.style.width = "";
				};
				const onPointerDown = (e) => {
					if (e.detail === 2) {
						resetToDefaultWidth();
						return;
					}
					e.preventDefault();
					const startX = e.clientX;
					const startWidth = panel.offsetWidth;
					document.body.style.cursor = "col-resize";
					document.body.style.userSelect = "none";
					const onMove = (e) => {
						const diff = resolvedEdge === "right" ? e.clientX - startX : startX - e.clientX;
						applyWidth(startWidth + diff);
					};
					const onUp = () => {
						document.body.style.cursor = "";
						document.body.style.userSelect = "";
						document.removeEventListener("pointermove", onMove);
						document.removeEventListener("pointerup", onUp);
						setStoredWidth(storageKey, panel.offsetWidth);
					};
					document.addEventListener("pointermove", onMove);
					document.addEventListener("pointerup", onUp);
				};
				const onDoubleClick = () => {
					resetToDefaultWidth();
				};
				handle.addEventListener("pointerdown", onPointerDown);
				handle.addEventListener("dblclick", onDoubleClick);
				cleanup = () => {
					handle.removeEventListener("pointerdown", onPointerDown);
					handle.removeEventListener("dblclick", onDoubleClick);
					handle.remove();
				};
			});
			else {
				cleanup?.();
				cleanup = null;
			}
		});
		cleanupFns.push(() => {
			cleanup?.();
			cleanup = null;
		});
	}
	onUnmounted(() => {
		cleanupFns.forEach((fn) => fn());
	});
	return { makeResizable };
}
// resources/js/pages/layout/PanelLayout.vue
var PanelLayout_exports = /* @__PURE__ */ __exportAll({ default: () => PanelLayout_default });
var minWidth = 175;
var maxWidth = 450;
var paddingOffset = "0.5rem";
var _sfc_main = {
	__name: "PanelLayout",
	setup(__props, { expose: __expose }) {
		__expose();
		const breakpoints = {
			narrow: 1250,
			compact: 1400
		};
		const left = {
			ref: useTemplateRef("leftPanel"),
			active: ref(false),
			edge: "right",
			defaults: [
				minWidth,
				270,
				300
			],
			storageKey: "statamic.panels.left-width"
		};
		const right = {
			ref: useTemplateRef("rightPanel"),
			active: ref(false),
			edge: "left",
			defaults: [
				300,
				290,
				340
			],
			storageKey: "statamic.panels.right-width"
		};
		provide("leftPanelActive", left.active);
		provide("rightPanelActive", right.active);
		function formatWidth(panel, width) {
			return panel === left ? `calc(${width}px - ${paddingOffset})` : `${width}px`;
		}
		function getDefaultWidth(panel) {
			const w = window.innerWidth;
			if (w < breakpoints.narrow) return formatWidth(panel, panel.defaults[0]);
			if (w < breakpoints.compact) return formatWidth(panel, panel.defaults[1]);
			return formatWidth(panel, panel.defaults[2]);
		}
		const { makeResizable } = useResizable();
		[left, right].forEach((panel) => {
			makeResizable(panel.ref, panel.active, {
				edge: panel.edge,
				minWidth,
				maxWidth,
				defaultWidth: () => getDefaultWidth(panel),
				storageKey: panel.storageKey
			});
			panel.priorDefaults = new Set(panel.defaults.map((d) => formatWidth(panel, d)));
		});
		function applyBreakpointDefaults() {
			[left, right].forEach((panel) => {
				if (!panel.active.value) return;
				if (localStorage.getItem(panel.storageKey)) return;
				const el = panel.ref.value;
				if (!el) return;
				const current = el.style.width;
				if (!current || panel.priorDefaults.has(current)) el.style.width = getDefaultWidth(panel);
			});
		}
		watch([left.active, right.active], () => {
			const el = document.getElementById("main-content");
			if (!el) return;
			if (left.active.value || right.active.value) el.setAttribute("data-panels-showing", "");
			else el.removeAttribute("data-panels-showing");
		});
		onMounted(() => {
			applyBreakpointDefaults();
			window.addEventListener("resize", applyBreakpointDefaults);
		});
		onUnmounted(() => {
			window.removeEventListener("resize", applyBreakpointDefaults);
			document.getElementById("main-content")?.removeAttribute("data-panels-showing");
		});
		const __returned__ = {
			minWidth,
			maxWidth,
			paddingOffset,
			breakpoints,
			left,
			right,
			formatWidth,
			getDefaultWidth,
			makeResizable,
			applyBreakpointDefaults,
			provide,
			ref,
			watch,
			onMounted,
			onUnmounted,
			useTemplateRef,
			get useResizable() {
				return useResizable;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = ["data-left-panel"];
var _hoisted_2 = ["data-right-panel"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [
		(openBlock(), createBlock(Teleport, {
			defer: "",
			to: "#main-content"
		}, [withDirectives(createBaseVNode("div", {
			"data-left-panel": $setup.left.active ? "" : void 0,
			ref: "leftPanel",
			id: "left-panel",
			tabindex: "-1",
			style: { "order": "-1" },
			class: "sticky top-0 overflow-y-scroll overscroll-y-contain overflow-x-clip h-full min-[1000px]:grid mx-auto focus:outline-none pe-2"
		}, null, 8, _hoisted_1), [[vShow, $setup.left.active]])])),
		(openBlock(), createBlock(Teleport, {
			defer: "",
			to: "#main-content"
		}, [withDirectives(createBaseVNode("div", {
			"data-right-panel": $setup.right.active ? "" : void 0,
			ref: "rightPanel",
			id: "right-panel",
			tabindex: "-1",
			style: { "order": "2" },
			class: "sticky top-0 overflow-y-scroll overscroll-y-contain h-full min-[1000px]:-mr-2 min-[1000px]:grid mx-auto focus:outline-none ps-2"
		}, null, 8, _hoisted_2), [[vShow, $setup.right.active]])])),
		renderSlot(_ctx.$slots, "default")
	], 64);
}
var PanelLayout_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "PanelLayout.vue"]]);
export { PanelLayout_exports as n, PanelLayout_default as t };
