declare module '*.vue' {
	import type { DefineComponent } from 'vue'
	const component: DefineComponent<object, object, unknown>
	export default component
}

declare module '@nextcloud/vue/*'
declare module '@nextcloud/router'
declare module '@nextcloud/l10n'
declare module '@nextcloud/logger'
declare module '@nextcloud/initial-state'
declare module '@nextcloud/password-confirmation'
