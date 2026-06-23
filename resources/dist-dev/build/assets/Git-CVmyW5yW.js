import { At as toDisplayString, B as openBlock, C as createVNode, S as createTextVNode, W as renderList, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, it as withCtx, u as withModifiers, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router } from "./index.esm-B4SStoAz.js";
import { Hn as Panel_default, Jn as Heading_default, Ut as Header_default, li as Button_default, n as DocsCallout_default, ui as Badge_default } from "./ui-BfR7XN6t.js";
import { E as GitStatus_default, r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/utilities/Git.vue
var _sfc_main = {
	__name: "Git",
	props: ["statuses", "commitUrl"],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		const submitting = ref(false);
		function commit() {
			submitting.value = true;
			router.post(props.commitUrl, {}, { onFinish: () => {
				submitting.value = false;
			} });
		}
		const __returned__ = {
			props,
			submitting,
			commit,
			ref,
			get router() {
				return router;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get CardPanel() {
				return Panel_default;
			},
			get Badge() {
				return Badge_default;
			},
			get Heading() {
				return Heading_default;
			},
			get DocsCallout() {
				return DocsCallout_default;
			},
			GitStatus: GitStatus_default
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
var _hoisted_2 = { class: "space-y-4" };
var _hoisted_3 = { class: "flex flex-wrap gap-2" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [
		createVNode($setup["Head"], { title: [_ctx.__("Git"), _ctx.__("Utilities")] }, null, 8, ["title"]),
		createBaseVNode("form", { onSubmit: withModifiers($setup.commit, ["prevent"]) }, [createVNode($setup["Header"], {
			title: _ctx.__("Git"),
			icon: "git"
		}, {
			default: withCtx(() => [createVNode($setup["Button"], {
				type: "submit",
				variant: "primary",
				text: _ctx.__("Commit Changes"),
				disabled: !$props.statuses || $setup.submitting
			}, null, 8, ["text", "disabled"])]),
			_: 1
		}, 8, ["title"])], 32),
		$props.statuses ? (openBlock(true), createElementBlock(Fragment, { key: 0 }, renderList($props.statuses, (status) => {
			return openBlock(), createBlock($setup["CardPanel"], {
				key: status.path,
				heading: _ctx.__("Repository"),
				subheading: status.path
			}, {
				default: withCtx(() => [createBaseVNode("div", _hoisted_2, [createBaseVNode("div", _hoisted_3, [
					createVNode($setup["Badge"], {
						prepend: _ctx.__("Affected files"),
						text: status.totalCount
					}, null, 8, ["prepend", "text"]),
					status.addedCount ? (openBlock(), createBlock($setup["Badge"], {
						key: 0,
						prepend: _ctx.__("Added"),
						color: "green",
						text: status.addedCount
					}, null, 8, ["prepend", "text"])) : createCommentVNode("", true),
					status.modifiedCount ? (openBlock(), createBlock($setup["Badge"], {
						key: 1,
						prepend: _ctx.__("Modified"),
						color: "yellow",
						text: status.modifiedCount
					}, null, 8, ["prepend", "text"])) : createCommentVNode("", true),
					status.deletedCount ? (openBlock(), createBlock($setup["Badge"], {
						key: 2,
						prepend: _ctx.__("Deleted"),
						color: "red",
						text: status.deletedCount
					}, null, 8, ["prepend", "text"])) : createCommentVNode("", true)
				]), createVNode($setup["GitStatus"], { status: status.status }, null, 8, ["status"])])]),
				_: 2
			}, 1032, ["heading", "subheading"]);
		}), 128)) : (openBlock(), createBlock($setup["CardPanel"], {
			key: 1,
			heading: _ctx.__("Repository")
		}, {
			default: withCtx(() => [createVNode($setup["Heading"], null, {
				default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("statamic::messages.git_nothing_to_commit")), 1)]),
				_: 1
			})]),
			_: 1
		}, 8, ["heading"])),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("the Git Integration"),
			url: "git-integration"
		}, null, 8, ["topic"])
	]);
}
var Git_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Git.vue"]]);
//#endregion
export { Git_default as default };
