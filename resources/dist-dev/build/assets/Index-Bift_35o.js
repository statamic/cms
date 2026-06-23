import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, W as renderList, _ as createBlock, _t as ref, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { c as router, i as link_default } from "./index.esm-B4SStoAz.js";
import { Kn as Subheading_default, Ut as Header_default, i as Listing_default, li as Button_default, n as DocsCallout_default, r as Item_default$1, tn as Item_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/components/fieldsets/FieldsetDeleter.vue
var _sfc_main$2 = {
	props: {
		resource: { type: Object },
		resourceTitle: { type: String },
		route: { type: String },
		redirect: { type: String },
		reload: { type: Boolean }
	},
	data() {
		return {
			deleting: false,
			redirectFromServer: null
		};
	},
	computed: {
		title() {
			return data_get(this.resource, "title", this.resourceTitle);
		},
		modalTitle() {
			return __("Delete :resource", { resource: __(this.title) });
		},
		deleteUrl() {
			let url = data_get(this.resource, "delete_url", this.route);
			if (!url) console.error("ResourceDeleter cannot find delete url");
			return url;
		},
		redirectUrl() {
			return this.redirect || this.redirectFromServer;
		}
	},
	methods: {
		confirm() {
			this.deleting = true;
		},
		confirmed() {
			this.$axios.delete(this.deleteUrl).then((response) => {
				this.redirectFromServer = data_get(response, "data.redirect");
				this.success();
			}).catch(() => {
				this.$toast.error(__("Something went wrong"));
			});
		},
		success() {
			if (this.redirectUrl) {
				location.href = this.redirectUrl;
				return;
			}
			if (this.reload) {
				location.reload();
				return;
			}
			this.$toast.success(__("Deleted"));
			this.$emit("deleted");
		},
		cancel() {
			this.deleting = false;
		}
	}
};
var _hoisted_1$1 = { key: 1 };
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_description = resolveComponent("ui-description");
	const _component_ui_badge = resolveComponent("ui-badge");
	const _component_confirmation_modal = resolveComponent("confirmation-modal");
	return openBlock(), createBlock(_component_confirmation_modal, {
		open: $data.deleting,
		title: $options.modalTitle,
		buttonText: _ctx.__("Delete"),
		danger: true,
		disabled: Object.keys($props.resource.imported_by).length > 0,
		onConfirm: $options.confirmed,
		onCancel: $options.cancel
	}, {
		default: withCtx(() => [Object.keys($props.resource.imported_by).length > 0 ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [createVNode(_component_ui_description, null, {
			default: withCtx(() => [createTextVNode(toDisplayString(_ctx.__("Before you can delete this fieldset, you need to remove references to it in blueprints and fieldsets:")), 1)]),
			_: 1
		}), (openBlock(true), createElementBlock(Fragment, null, renderList($props.resource.imported_by, (items, group) => {
			return openBlock(), createElementBlock("div", null, [(openBlock(true), createElementBlock(Fragment, null, renderList(items, (item) => {
				return openBlock(), createBlock(_component_ui_badge, {
					key: item.handle,
					text: item.title,
					prepend: group
				}, null, 8, ["text", "prepend"]);
			}), 128))]);
		}), 256))], 64)) : (openBlock(), createElementBlock("p", _hoisted_1$1, toDisplayString(_ctx.__("Are you sure you want to delete this item?")), 1))]),
		_: 1
	}, 8, [
		"open",
		"title",
		"buttonText",
		"disabled",
		"onConfirm",
		"onCancel"
	]);
}
var FieldsetDeleter_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$2], ["__file", "FieldsetDeleter.vue"]]);
//#endregion
//#region resources/js/components/fieldsets/FieldsetResetter.vue
var _sfc_main$1 = {
	props: {
		resource: { type: Object },
		resourceTitle: { type: String },
		route: { type: String },
		redirect: { type: String },
		reload: { type: Boolean }
	},
	data() {
		return {
			resetting: false,
			redirectFromServer: null
		};
	},
	computed: {
		title() {
			return data_get(this.resource, "title", this.resourceTitle);
		},
		modalTitle() {
			return __("Reset :resource", { resource: this.title });
		},
		modalBody() {
			return __("Are you sure you want to reset this item?");
		},
		resetUrl() {
			let url = data_get(this.resource, "reset_url", this.route);
			if (!url) console.error("FieldsetResetter cannot find reset url");
			return url;
		},
		redirectUrl() {
			return this.redirect || this.redirectFromServer;
		}
	},
	methods: {
		confirm() {
			this.resetting = true;
		},
		confirmed() {
			this.$axios.delete(this.resetUrl).then((response) => {
				this.redirectFromServer = data_get(response, "data.redirect");
				this.success();
			}).catch(() => {
				this.$toast.error(__("Something went wrong"));
			});
		},
		success() {
			if (this.redirectUrl) {
				router.get(this.redirectUrl);
				return;
			}
			if (this.reload) {
				router.reload();
				return;
			}
			this.$toast.success(__("Reset"));
			this.$emit("reset");
		},
		cancel() {
			this.resetting = false;
		}
	}
};
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_confirmation_modal = resolveComponent("confirmation-modal");
	return openBlock(), createBlock(_component_confirmation_modal, {
		open: $data.resetting,
		title: $options.modalTitle,
		bodyText: $options.modalBody,
		buttonText: _ctx.__("Reset"),
		danger: true,
		onConfirm: $options.confirmed,
		onCancel: $options.cancel
	}, null, 8, [
		"open",
		"title",
		"bodyText",
		"buttonText",
		"onConfirm",
		"onCancel"
	]);
}
var FieldsetResetter_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "FieldsetResetter.vue"]]);
//#endregion
//#region resources/js/pages/fieldsets/Index.vue
var _sfc_main = {
	__name: "Index",
	props: ["fieldsets", "createUrl"],
	setup(__props, { expose: __expose }) {
		__expose();
		const deleters = ref({});
		const resetters = ref({});
		const columns = ref([
			{
				label: __("Title"),
				field: "title"
			},
			{
				label: __("Handle"),
				field: "handle",
				width: "25%"
			},
			{
				label: __("Fields"),
				field: "fields",
				width: "15%"
			}
		]);
		const reloadPage = () => router.reload();
		const __returned__ = {
			deleters,
			resetters,
			columns,
			reloadPage,
			ref,
			get Link() {
				return link_default;
			},
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
			get DocsCallout() {
				return DocsCallout_default;
			},
			get Listing() {
				return Listing_default;
			},
			get DropdownItem() {
				return Item_default;
			},
			get CommandPaletteItem() {
				return Item_default$1;
			},
			get Subheading() {
				return Subheading_default;
			},
			FieldsetDeleter: FieldsetDeleter_default,
			FieldsetResetter: FieldsetResetter_default
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
var _hoisted_2 = { class: "space-y-6 starting-style-transition-children" };
var _hoisted_3 = ["textContent"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock(Fragment, null, [createVNode($setup["Head"], { title: _ctx.__("Fieldsets") }, null, 8, ["title"]), createBaseVNode("div", _hoisted_1, [
		createVNode($setup["Header"], {
			title: _ctx.__("Fieldsets"),
			icon: "fieldsets"
		}, {
			default: withCtx(() => [createVNode($setup["CommandPaletteItem"], {
				category: "Actions",
				text: _ctx.__("Create Fieldset"),
				icon: "fieldsets",
				url: $props.createUrl
			}, {
				default: withCtx(({ text, url }) => [createVNode($setup["Button"], {
					href: url,
					text,
					variant: "primary"
				}, null, 8, ["href", "text"])]),
				_: 1
			}, 8, ["text", "url"])]),
			_: 1
		}, 8, ["title"]),
		createBaseVNode("section", _hoisted_2, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.fieldsets, (rows, key) => {
			return openBlock(), createElementBlock("div", {
				key,
				class: "mb-4"
			}, [Object.keys($props.fieldsets).length > 1 ? (openBlock(), createBlock($setup["Subheading"], {
				key: 0,
				size: "lg",
				class: "mb-2",
				text: key
			}, null, 8, ["text"])) : createCommentVNode("", true), createVNode($setup["Listing"], {
				items: rows,
				columns: $setup.columns,
				"allow-search": false,
				"allow-customizing-columns": false,
				onRefreshing: $setup.reloadPage
			}, {
				"cell-title": withCtx(({ row: fieldset }) => [
					createVNode($setup["Link"], {
						href: fieldset.edit_url,
						textContent: toDisplayString(_ctx.__(fieldset.title))
					}, null, 8, ["href", "textContent"]),
					createVNode($setup["FieldsetResetter"], {
						ref_for: true,
						ref: (el) => $setup.resetters[fieldset.id] = el,
						resource: fieldset,
						reload: ""
					}, null, 8, ["resource"]),
					createVNode($setup["FieldsetDeleter"], {
						ref_for: true,
						ref: (el) => $setup.deleters[fieldset.id] = el,
						resource: fieldset,
						reload: ""
					}, null, 8, ["resource"])
				]),
				"cell-handle": withCtx(({ value }) => [createBaseVNode("span", {
					class: "font-mono text-xs",
					textContent: toDisplayString(value)
				}, null, 8, _hoisted_3)]),
				"prepended-row-actions": withCtx(({ row: fieldset }) => [
					createVNode($setup["DropdownItem"], {
						text: _ctx.__("Edit"),
						icon: "edit",
						href: fieldset.edit_url
					}, null, 8, ["text", "href"]),
					fieldset.is_resettable ? (openBlock(), createBlock($setup["DropdownItem"], {
						key: 0,
						text: _ctx.__("Reset"),
						icon: "history",
						variant: "destructive",
						onClick: ($event) => $setup.resetters[fieldset.id].confirm()
					}, null, 8, ["text", "onClick"])) : createCommentVNode("", true),
					fieldset.is_deletable ? (openBlock(), createBlock($setup["DropdownItem"], {
						key: 1,
						text: _ctx.__("Delete"),
						icon: "trash",
						variant: "destructive",
						onClick: ($event) => $setup.deleters[fieldset.id].confirm()
					}, null, 8, ["text", "onClick"])) : createCommentVNode("", true)
				]),
				_: 1
			}, 8, ["items", "columns"])]);
		}), 128))]),
		createVNode($setup["DocsCallout"], {
			topic: _ctx.__("Blueprints"),
			url: "blueprints"
		}, null, 8, ["topic"])
	])], 64);
}
var Index_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Index.vue"]]);
//#endregion
export { Index_default as default };
