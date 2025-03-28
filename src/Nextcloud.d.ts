interface EscapeOptions {
	escape?: boolean;
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
declare namespace OC {
	namespace Util {
		function humanFileSize(size: number): string;

		function computerFileSize(size: string): number;
	}

	namespace dialogs {
		function alert(text: string, title: string, callback: () => void, modal?: boolean): void;

		function info(text: string, title: string, callback: () => void, modal?: boolean): void;

		function confirm(text: string, title: string, callback: (result: boolean) => void, modal?: boolean): void;

		function confirmHtml(text: string, title: string, callback: (result: boolean) => void, modal?: boolean): void;

		function prompt(text: string, title: string, callback: (ok: boolean, result: string) => void, modal?: boolean, name?: string, password?: boolean): void;

		function filepicker(title: string, callback: (result: string | string[]) => void, multiselect?: boolean, mimetypeFilter?: string, modal?: boolean): void;
	}

	namespace Share {
		const SHARE_TYPE_USER = 0;
		const SHARE_TYPE_GROUP = 1;
		const SHARE_TYPE_LINK = 3;
		const SHARE_TYPE_CIRCLE = 7;
	}

	interface Plugin<T> {
		name?: string;
		attach: (instance: T, options: any) => void;
		detach?: (instance: T, options: any) => void;
	}

	namespace Plugins {
		function register(scope: string, plugin: OC.Plugin<any>): void;
		function attach(targetName: string, targetObject: any, options: any): void;
		function detach(targetName: string, targetObject: any, options: any): void;
		function getPlugins(): OC.Plugin<any>[];
	}

	namespace Search {
		interface Core {
			setFilter: (app: string, callback: (query: string) => void) => void;
		}
	}

	namespace PasswordConfirmation {
		function requiresPasswordConfirmation(): boolean;

		function requirePasswordConfirmation(cb: () => void): void;
	}

	function generateUrl(url: string, parameters?: { [key: string]: string|number }, options?: EscapeOptions)

	function linkToOCS(service: string, version: number): string;

	function linkToRemote(path: string): string;

	function imagePath(app: string, file: string): string;

	function filePath(app: string, type: string, file: string): string;

	const PERMISSION_CREATE = 4;
	const PERMISSION_READ = 1;
	const PERMISSION_UPDATE = 2;
	const PERMISSION_DELETE = 8;
	const PERMISSION_SHARE = 16;
	const PERMISSION_ALL = 31;

	const currentUser: string;

	function getCurrentUser(): {uid: string; displayName: string}

	const requestToken: string;

	const config: {
		blacklist_files_regex: string;
		enable_avatars: boolean;
		last_password_link: string | null;
		modRewriteWorking: boolean;
		session_keepalive: boolean;
		session_lifetime: boolean;
		'sharing.maxAutocompleteResults': number;
		'sharing.minSearchStringLength': number;
		version: string;
		versionString: string;
	};
}

declare function t(app: string, string: string, vars?: { [key: string]: string }, count?: number, options?: EscapeOptions): string;

declare function n(app: string, singular: string, plural: string, number: number, vars?: { [key: string]: string }): string;

declare module 'NC' {
	export interface OCSResult<T> {
		ocs: {
			data: T;
			meta: {
				status: 'ok' | 'failure';
				message: string;
				statuscode: number;
				totalitems: number;
				itemsperpage: number;
			};
		};
	}
}

declare namespace OCA {
    namespace Files {
        const fileActions: any;
        const App: any;
    }

    namespace Sharing {
        const PublicApp: any;
    }
}

declare namespace OCP {
	namespace AppConfig {
		function setValue(appId: string, key: string, value: any): void;
	}
	namespace Files {
		namespace Router {
			namespace query {
				const dir: null | string
			}
		}
	}
}