import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, W as renderList, _ as createBlock, b as createSlots, f as Fragment, g as createBaseVNode, it as withCtx, v as createCommentVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Ci as Icon_default, Dt as Pagination_default } from "./ui-BfR7XN6t.js";
import { D as DateFormatter } from "./api-BR1uuoCk.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/components/updater/Release.vue
var _sfc_main$2 = {
	props: {
		release: {
			type: Object,
			required: true
		},
		package: {
			type: String,
			required: true
		},
		packageName: {
			type: String,
			required: true
		},
		showActions: { type: Boolean }
	},
	data() {
		return { confirmationPrompt: null };
	},
	computed: {
		date() {
			return DateFormatter.format(this.release.date, "date");
		},
		body() {
			return markdown(this.release.body).replaceAll("[new]", "<span class=\"label\" style=\"background: #5bc0de;\">NEW</span>").replaceAll("[fix]", "<span class=\"label\" style=\"background: #5cb85c;\">FIX</span>").replaceAll("[break]", "<span class=\"label\" style=\"background: #d9534f;\">BREAK</span>").replaceAll("[na]", "<span class=\"label\" style=\"background: #e8e8e8;\">N/A</span>");
		},
		installButtonText() {
			if (this.release.type === "current") return __("Current Version");
			if (this.release.latest) return __("Update to Latest");
			if (this.release.type === "upgrade") return __("Update to :version", { version: this.release.version });
			return __("Downgrade to :version", { version: this.release.version });
		},
		confirmationText() {
			if (this.release.latest) return `${__("messages.updater_update_to_latest_command")}:`;
			return `${__("messages.updater_require_version_command")}:`;
		},
		command() {
			if (this.release.latest) return `composer update ${this.package}`;
			return `composer require "${this.package} ${this.release.version}"`;
		},
		link() {
			return __("Learn more about :link", { link: `<a href="https://statamic.dev/updating" target="_blank" class="font-medium underline text-blue-500 dark:text-blue-400">${__("updating Statamic")}</a>` }) + ".";
		}
	},
	mounted() {
		this.addToCommandPalette();
	},
	methods: { addToCommandPalette() {
		if (!this.release.latest) return;
		if (this.release.type === "current") return;
		Statamic.$commandPalette.add({
			category: Statamic.$commandPalette.category.Actions,
			text: [__("Update to Latest"), __("Get Command")],
			icon: "clipboard",
			action: () => this.$refs.getCommandButton.$el.click()
		});
	} }
};
var _hoisted_1$2 = { class: "flex items-center gap-2" };
var _hoisted_2$1 = { class: "prose prose-sm prose-zinc prose-headings:font-medium space-y-3" };
var _hoisted_3$1 = ["textContent"];
var _hoisted_4 = ["innerHTML"];
var _hoisted_5 = ["innerHTML"];
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_heading = resolveComponent("ui-heading");
	const _component_ui_badge = resolveComponent("ui-badge");
	const _component_ui_subheading = resolveComponent("ui-subheading");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_ui_input = resolveComponent("ui-input");
	const _component_ui_modal = resolveComponent("ui-modal");
	const _component_ui_panel_header = resolveComponent("ui-panel-header");
	const _component_ui_card = resolveComponent("ui-card");
	const _component_ui_panel = resolveComponent("ui-panel");
	return openBlock(), createBlock(_component_ui_panel, null, {
		default: withCtx(() => [createVNode(_component_ui_panel_header, { class: "flex items-center justify-between" }, {
			default: withCtx(() => [createBaseVNode("div", null, [createBaseVNode("div", _hoisted_1$2, [createVNode(_component_ui_heading, { text: $props.release.version }, null, 8, ["text"]), $props.release.security ? (openBlock(), createBlock(_component_ui_badge, {
				key: 0,
				text: _ctx.__("Security"),
				color: "red",
				size: "sm"
			}, null, 8, ["text"])) : createCommentVNode("", true)]), createVNode(_component_ui_subheading, { text: `${_ctx.__("Released on :date", { date: $options.date })}` }, null, 8, ["text"])]), createVNode(_component_ui_modal, {
				title: _ctx.__("Update to :version", { version: $props.release.version }),
				blur: ""
			}, {
				trigger: withCtx(() => [$props.showActions ? (openBlock(), createBlock(_component_ui_button, {
					key: 0,
					ref: "getCommandButton",
					icon: "clipboard",
					size: "sm",
					disabled: $props.release.type === "current",
					text: _ctx.__("Get Command")
				}, null, 8, ["disabled", "text"])) : createCommentVNode("", true)]),
				default: withCtx(() => [createBaseVNode("div", _hoisted_2$1, [
					createBaseVNode("p", { textContent: toDisplayString($options.confirmationText) }, null, 8, _hoisted_3$1),
					createVNode(_component_ui_input, {
						readonly: "",
						copyable: "",
						"model-value": $options.command,
						class: "dark",
						inputClass: "font-mono text-sm"
					}, null, 8, ["model-value"]),
					createBaseVNode("p", { innerHTML: $options.link }, null, 8, _hoisted_4)
				])]),
				_: 1
			}, 8, ["title"])]),
			_: 1
		}), createVNode(_component_ui_card, null, {
			default: withCtx(() => [createBaseVNode("div", {
				innerHTML: $options.body,
				class: "prose prose-sm prose-zinc prose-headings:font-medium"
			}, null, 8, _hoisted_5)]),
			_: 1
		})]),
		_: 1
	});
}
//#endregion
//#region resources/js/components/updater/Updater.vue
var _sfc_main$1 = {
	components: {
		Release: /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$2, [["render", _sfc_render$2], ["__file", "Release.vue"]]),
		Icon: Icon_default,
		Pagination: Pagination_default
	},
	props: [
		"slug",
		"package",
		"name"
	],
	data() {
		return {
			gettingChangelog: true,
			changelog: [],
			currentVersion: null,
			latestRelease: null,
			showingUnlicensedReleases: false,
			page: 1,
			perPage: 10,
			meta: {}
		};
	},
	computed: {
		toEleven() {
			return { timeout: Statamic.$config.get("ajaxTimeout") };
		},
		showActions() {
			return !this.gettingChangelog;
		},
		onLatestVersion() {
			return this.currentVersion && this.currentVersion == this.latestVersion;
		},
		securityUpdateAvailable() {
			return this.currentVersion && this.changelog.filter((release) => release.type === "upgrade").some((release) => release.security);
		},
		licensedReleases() {
			return this.changelog.filter((release) => release.licensed);
		},
		unlicensedReleases() {
			return this.changelog.filter((release) => !release.licensed);
		},
		hasUnlicensedReleases() {
			return this.unlicensedReleases.length > 0;
		},
		latestVersion() {
			return this.latestRelease && this.latestRelease.version;
		},
		link() {
			return __("Learn more about :link", { link: `<a href="https://statamic.dev/updating" target="_blank">${__("Updates")}</a>` }) + ".";
		}
	},
	created() {
		this.getChangelog();
	},
	methods: {
		getChangelog() {
			this.gettingChangelog = true;
			this.$axios.get(cp_url(`/updater/${this.slug}/changelog`), { params: {
				page: this.page,
				perPage: this.perPage
			} }).then((response) => {
				this.gettingChangelog = false;
				this.changelog = response.data.changelog;
				this.currentVersion = response.data.currentVersion;
				this.meta = response.data.meta;
				if (this.page === 1 && response.data.changelog.length > 0) this.latestRelease = response.data.changelog[0];
			});
		},
		setPage(page) {
			this.page = page;
			this.getChangelog();
		},
		setPerPage(perPage) {
			this.perPage = perPage;
			this.page = 1;
			this.getChangelog();
		}
	}
};
var _hoisted_1$1 = { class: "max-w-page mx-auto" };
var _hoisted_2 = ["textContent"];
var _hoisted_3 = ["textContent"];
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_badge = resolveComponent("ui-badge");
	const _component_ui_header = resolveComponent("ui-header");
	const _component_Icon = resolveComponent("Icon");
	const _component_ui_card = resolveComponent("ui-card");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_release = resolveComponent("release");
	const _component_Pagination = resolveComponent("Pagination");
	return openBlock(), createElementBlock("div", _hoisted_1$1, [
		createVNode(_component_ui_header, {
			title: $props.name,
			icon: "updates"
		}, createSlots({ _: 2 }, [!$data.gettingChangelog ? {
			name: "actions",
			fn: withCtx(() => [createTextVNode(toDisplayString($data.currentVersion) + " ", 1), $options.onLatestVersion ? (openBlock(), createBlock(_component_ui_badge, {
				key: 0,
				text: _ctx.__("Up to date"),
				color: "green",
				size: "lg",
				icon: "checkmark"
			}, null, 8, ["text"])) : $options.securityUpdateAvailable ? (openBlock(), createBlock(_component_ui_badge, {
				key: 1,
				text: _ctx.__("Security update available"),
				color: "red",
				size: "lg",
				icon: "alert-warning-exclamation-mark"
			}, null, 8, ["text"])) : (openBlock(), createBlock(_component_ui_badge, {
				key: 2,
				text: _ctx.__("Update available"),
				color: "amber",
				size: "lg",
				icon: "alert-warning-exclamation-mark"
			}, null, 8, ["text"]))]),
			key: "0"
		} : void 0]), 1032, ["title"]),
		$data.gettingChangelog ? (openBlock(), createBlock(_component_ui_card, {
			key: 0,
			class: "text-center starting-style-transition"
		}, {
			default: withCtx(() => [createVNode(_component_Icon, { name: "loading" })]),
			_: 1
		})) : createCommentVNode("", true),
		!$data.showingUnlicensedReleases && $options.hasUnlicensedReleases ? (openBlock(), createElementBlock("div", {
			key: 1,
			class: "mb-6 flex cursor-pointer items-center justify-between rounded-sm border border-dashed border-yellow-dark bg-yellow p-4 text-xs",
			onClick: _cache[0] || (_cache[0] = ($event) => $data.showingUnlicensedReleases = true)
		}, [createBaseVNode("div", null, [createBaseVNode("h4", { textContent: toDisplayString(_ctx.__("messages.addon_has_more_releases_beyond_license_heading")) }, null, 8, _hoisted_2), createBaseVNode("p", { textContent: toDisplayString(_ctx.__("messages.addon_has_more_releases_beyond_license_body")) }, null, 8, _hoisted_3)]), createVNode(_component_ui_button, {
			size: "sm",
			textContent: toDisplayString(_ctx.__("View additional releases"))
		}, null, 8, ["textContent"])])) : createCommentVNode("", true),
		$data.showingUnlicensedReleases ? (openBlock(true), createElementBlock(Fragment, { key: 2 }, renderList($options.unlicensedReleases, (release) => {
			return openBlock(), createBlock(_component_release, {
				key: release.version,
				release,
				"package-name": $props.name,
				package: $props.package,
				"show-actions": $options.showActions
			}, null, 8, [
				"release",
				"package-name",
				"package",
				"show-actions"
			]);
		}), 128)) : createCommentVNode("", true),
		(openBlock(true), createElementBlock(Fragment, null, renderList($options.licensedReleases, (release) => {
			return openBlock(), createBlock(_component_release, {
				key: release.version,
				release,
				"package-name": $props.name,
				package: $props.package,
				"show-actions": $options.showActions
			}, null, 8, [
				"release",
				"package-name",
				"package",
				"show-actions"
			]);
		}), 128)),
		$data.meta.last_page > 1 ? (openBlock(), createBlock(_component_Pagination, {
			key: 3,
			class: "mt-6",
			"resource-meta": $data.meta,
			"per-page": $data.perPage,
			onPageSelected: $options.setPage,
			onPerPageChanged: $options.setPerPage
		}, null, 8, [
			"resource-meta",
			"per-page",
			"onPageSelected",
			"onPerPageChanged"
		])) : createCommentVNode("", true)
	]);
}
var Updater_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main$1, [["render", _sfc_render$1], ["__file", "Updater.vue"]]);
//#endregion
//#region resources/js/pages/updater/Show.vue
var _sfc_main = {
	__name: "Show",
	props: [
		"slug",
		"package",
		"name"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			Head: Head_default,
			Updater: Updater_default
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
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	return openBlock(), createElementBlock("div", _hoisted_1, [createVNode($setup["Head"], { title: [$props.name, _ctx.__("Updates")] }, null, 8, ["title"]), createVNode($setup["Updater"], {
		slug: $props.slug,
		package: $props.package,
		name: $props.name
	}, null, 8, [
		"slug",
		"package",
		"name"
	])]);
}
var Show_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Show.vue"]]);
//#endregion
export { Show_default as default };
