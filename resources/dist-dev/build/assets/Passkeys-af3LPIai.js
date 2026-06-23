import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, _ as createBlock, _t as ref, et as watch, f as Fragment, g as createBaseVNode, h as computed, it as withCtx, l as withKeys, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { G as axios, c as router } from "./index.esm-B4SStoAz.js";
import { At as Close_default, Kt as Menu_default, Ut as Header_default, Vt as Input_default, Wt as Field_default, i as Listing_default, jt as Modal_default, li as Button_default, qt as Item_default, tn as Item_default$1, vn as ConfirmationModal_default } from "./ui-BfR7XN6t.js";
import { r as Head_default, t as toggleArchitecturalBackground } from "./index-Bu3QBQkS.js";
import { i as browserSupportsWebAuthn, n as startRegistration } from "./esm-PPJ3V_AU.js";
//#region resources/js/pages/users/Passkeys.vue
var _sfc_main = {
	__name: "Passkeys",
	props: [
		"passkeys",
		"createUrl",
		"storeUrl",
		"deleteUrl"
	],
	setup(__props, { expose: __expose }) {
		__expose();
		const props = __props;
		watch(() => props.passkeys, (passkeys) => toggleArchitecturalBackground(passkeys.length === 0), { immediate: true });
		const error = ref(null);
		const showCreateModal = ref(false);
		const showErrorModal = computed(() => !!error.value);
		const passkeyWaiting = ref(false);
		const passkeyName = ref("");
		watch(showCreateModal, (opened) => {
			if (!opened) passkeyName.value = "";
		});
		const columns = [{
			label: __("Name"),
			field: "name"
		}, {
			label: __("Last Login"),
			field: "last_login"
		}];
		function deletePasskey(passkey) {
			if (confirm(__("Are you sure?"))) axios.delete(passkey.delete_url).then(() => router.reload());
		}
		async function createPasskey() {
			if (!browserSupportsWebAuthn()) {
				alert(__("statamic::messages.passkeys_browser_unsupported"));
				return;
			}
			passkeyWaiting.value = true;
			const name = passkeyName.value || `${__("Passkey")} ${props.passkeys.length + 1}`;
			showCreateModal.value = false;
			const authOptionsResponse = await fetch(props.createUrl);
			let startRegistrationResponse;
			try {
				startRegistrationResponse = await startRegistration(await authOptionsResponse.json());
			} catch (e) {
				console.error(e);
				passkeyWaiting.value = false;
				return;
			}
			axios.post(props.storeUrl, {
				...startRegistrationResponse,
				name
			}).then((response) => {
				if (response && response.data.verified) {
					router.reload();
					return;
				}
				error.value = response.data.message;
			}).catch((e) => handleAxiosError(e)).finally(() => passkeyWaiting.value = false);
		}
		function handleAxiosError(e) {
			if (e.response) {
				const { message, errors } = e.response.data;
				error.value = message;
				return;
			}
			error.value = __("Something went wrong");
		}
		const __returned__ = {
			props,
			error,
			showCreateModal,
			showErrorModal,
			passkeyWaiting,
			passkeyName,
			columns,
			deletePasskey,
			createPasskey,
			handleAxiosError,
			computed,
			ref,
			watch,
			get startRegistration() {
				return startRegistration;
			},
			get browserSupportsWebAuthn() {
				return browserSupportsWebAuthn;
			},
			get router() {
				return router;
			},
			get axios() {
				return axios;
			},
			Head: Head_default,
			get Header() {
				return Header_default;
			},
			get Button() {
				return Button_default;
			},
			get EmptyStateMenu() {
				return Menu_default;
			},
			get EmptyStateItem() {
				return Item_default;
			},
			get Listing() {
				return Listing_default;
			},
			get DropdownItem() {
				return Item_default$1;
			},
			get Modal() {
				return Modal_default;
			},
			get ConfirmationModal() {
				return ConfirmationModal_default;
			},
			get Input() {
				return Input_default;
			},
			get ModalClose() {
				return Close_default;
			},
			get Field() {
				return Field_default;
			},
			get toggleArchitecturalBackground() {
				return toggleArchitecturalBackground;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "flex items-center justify-end space-x-3 pt-3 pb-1" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_date_time = resolveComponent("date-time");
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: _ctx.__("Passkeys") }, null, 8, ["title"]),
		createVNode($setup["Header"], {
			title: _ctx.__("Passkeys"),
			icon: "key"
		}, {
			actions: withCtx(() => [createVNode($setup["Button"], {
				variant: "primary",
				text: _ctx.__("Create Passkey"),
				onClick: _cache[0] || (_cache[0] = ($event) => $setup.showCreateModal = true),
				disabled: $setup.passkeyWaiting,
				loading: $setup.passkeyWaiting
			}, null, 8, [
				"text",
				"disabled",
				"loading"
			])]),
			_: 1
		}, 8, ["title"]),
		$props.passkeys.length ? (openBlock(), createBlock($setup["Listing"], {
			key: 0,
			items: $props.passkeys,
			columns: $setup.columns,
			"allow-search": false,
			"allow-customizing-columns": false
		}, {
			"cell-last_login": withCtx(({ value }) => [value ? (openBlock(), createBlock(_component_date_time, {
				key: 0,
				of: value,
				options: { relative: true }
			}, null, 8, ["of"])) : (openBlock(), createElementBlock(Fragment, { key: 1 }, [createTextVNode(toDisplayString(_ctx.__("Never")), 1)], 64))]),
			"prepended-row-actions": withCtx(({ row: passkey }) => [createVNode($setup["DropdownItem"], {
				onClick: ($event) => $setup.deletePasskey(passkey),
				text: _ctx.__("Delete"),
				icon: "trash",
				variant: "destructive"
			}, null, 8, ["onClick", "text"])]),
			_: 1
		}, 8, ["items"])) : (openBlock(), createBlock($setup["EmptyStateMenu"], {
			key: 1,
			heading: _ctx.__("statamic::messages.passkeys_configure_intro")
		}, {
			default: withCtx(() => [createVNode($setup["EmptyStateItem"], {
				onClick: _cache[1] || (_cache[1] = ($event) => $setup.showCreateModal = true),
				icon: "key",
				heading: _ctx.__("Create a Passkey"),
				description: _ctx.__("Get started by creating your first passkey.")
			}, null, 8, ["heading", "description"])]),
			_: 1
		}, 8, ["heading"])),
		createVNode($setup["ConfirmationModal"], {
			open: $setup.showErrorModal,
			title: _ctx.__("There was an error creating your passkey"),
			"body-text": $setup.error,
			cancellable: false,
			"button-text": _ctx.__("OK"),
			blur: "",
			"onUpdate:open": _cache[2] || (_cache[2] = ($event) => $setup.error = null)
		}, null, 8, [
			"open",
			"title",
			"body-text",
			"button-text"
		]),
		createVNode($setup["Modal"], {
			title: _ctx.__("Create a Passkey"),
			open: $setup.showCreateModal,
			"onUpdate:open": _cache[4] || (_cache[4] = ($event) => $setup.showCreateModal = $event),
			blur: ""
		}, {
			footer: withCtx(() => [createBaseVNode("div", _hoisted_1, [createVNode($setup["ModalClose"], null, {
				default: withCtx(() => [createVNode($setup["Button"], {
					variant: "ghost",
					text: _ctx.__("Cancel")
				}, null, 8, ["text"])]),
				_: 1
			}), createVNode($setup["Button"], {
				variant: "primary",
				text: _ctx.__("Create"),
				onClick: $setup.createPasskey
			}, null, 8, ["text"])])]),
			default: withCtx(() => [createVNode($setup["Field"], { label: _ctx.__("Name") }, {
				default: withCtx(() => [createVNode($setup["Input"], {
					modelValue: $setup.passkeyName,
					"onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.passkeyName = $event),
					onKeyup: withKeys($setup.createPasskey, ["enter"])
				}, null, 8, ["modelValue"])]),
				_: 1
			}, 8, ["label"])]),
			_: 1
		}, 8, ["title", "open"])
	], 64);
}
var Passkeys_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Passkeys.vue"]]);
//#endregion
export { Passkeys_default as default };
