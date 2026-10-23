import "./vue.esm-bundler-DDHDr1YM.js";
import { o as usePage } from "./index.esm-CMKtkbzu.js";
// resources/js/composables/page-props.js
function useStatamicPageProps() {
	const page = usePage();
	return new Proxy({}, { get(target, prop) {
		return page.props._statamic?.[prop];
	} });
}
export { useStatamicPageProps as t };
