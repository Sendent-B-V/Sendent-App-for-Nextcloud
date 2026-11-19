module.exports = {
	root: true,
	extends: ['@nextcloud/eslint-config/typescript'],
	rules: {
		'no-restricted-syntax': ['error', {
			// Matches OCP.AppConfig, window.OCP.AppConfig, OC.AppConfig, ...
			selector: "MemberExpression[property.name='AppConfig']",
			message: 'Do not write app config from the browser via OCP.AppConfig: since NC 33 it posts JSON, non-string values are rejected with 400 and failures are silent. Persist config in a Sendent controller instead (see TagController::create).',
		}],
	},
}
