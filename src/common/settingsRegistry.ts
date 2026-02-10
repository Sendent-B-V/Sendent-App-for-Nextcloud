/**
 * @copyright Copyright (c) 2026 Sendent B.V.
 *
 * @author Sendent B.V. <info@sendent.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
import type { SettingDefinition } from '../types/settings'

/**
 * Registry of all 64 setting definitions.
 * - key: numeric settingkeyid (used in API URLs)
 * - name: string settingkeyname (string identifier)
 * - templateId: 0=general, 1=advancedTheming, 2=teams
 */
export const settingsRegistry: SettingDefinition[] = [
	// ── General ──────────────────────────────────────────
	{
		key: 20,
		name: 'setlanguage',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		options: [
			{ value: 'en', label: 'English' },
			{ value: 'nl', label: 'Nederlands' },
			{ value: 'fr', label: 'Français' },
			{ value: 'de', label: 'Deutsch' },
			{ value: 'it', label: 'Italiano' },
			{ value: 'es', label: 'Español' },
			{ value: 'da', label: 'Dansk' },
			{ value: 'ru', label: 'Russkiy' },
		],
	},
	{
		key: 28,
		name: 'insertatcursor',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'At cursor' },
			{ value: 'False', label: 'Top of email body' },
		],
	},
	{
		key: 6,
		name: 'dateaddition',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Add upload date to folder path' },
			{ value: 'False', label: 'Do not add upload date to folder path' },
		],
	},
	{
		key: 22,
		name: 'debugmode',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 17,
		name: 'disablesettings',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		options: [
			{ value: 'False', label: 'Enabled' },
			{ value: 'True', label: 'Disabled' },
		],
	},
	{
		key: 19,
		name: 'passwordcontrolbehavior',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		options: [
			{ value: 'Off', label: 'Off' },
			{ value: 'BeforeSend', label: 'Before sending' },
			{ value: 'BeforeSendAndAfter', label: 'Before and after sending' },
		],
	},
	{
		key: 23,
		name: 'sendmode',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		options: [
			{ value: 'CurrentMail', label: 'Include in email body' },
			{ value: 'Separate', label: 'Send in separate email' },
			{ value: 'External', label: 'Use external service (like sms-gateway)' },
		],
	},
	{
		key: 30,
		name: 'htmlsnippetpassword',
		templateId: 0,
		section: 'General',
		inputType: 'textarea',
		visibilityRule: { dependsOn: 'sendmode', showWhen: 'Separate' },
	},
	{
		key: 401,
		name: 'statussync',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},

	// ── Talk ─────────────────────────────────────────────
	{
		key: 202,
		name: 'talkenabled',
		templateId: 0,
		section: 'Talk',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 201,
		name: 'generatetalkpassword',
		templateId: 0,
		section: 'Talk',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 203, name: 'talkhtml', templateId: 0, section: 'Talk', inputType: 'textarea',
	},
	{
		key: 209,
		name: 'talkpwsepenabled',
		templateId: 0,
		section: 'Talk',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 210, name: 'talkpwhtml', templateId: 0, section: 'Talk', inputType: 'textarea',
	},

	// ── Archiving ────────────────────────────────────────
	{
		key: 204,
		name: 'archivingenabled',
		templateId: 0,
		section: 'Archiving',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 205, name: 'patharchiving', templateId: 0, section: 'Archiving', inputType: 'text',
	},
	{
		key: 206,
		name: 'archivemode',
		templateId: 0,
		section: 'Archiving',
		inputType: 'select',
		options: [
			{ value: '1', label: 'Upload Attachments' },
			{ value: '2', label: 'Upload Attachments and original email' },
			{ value: '3', label: 'Upload Attachments, original email and use Secure Mail' },
		],
	},
	{
		key: 207,
		name: 'archivecreatefolder',
		templateId: 0,
		section: 'Archiving',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},

	// ── Domain Exceptions ────────────────────────────────
	{
		key: 2, name: 'attachmentdomainexceptionsinternal', templateId: 0, section: 'DomainExceptions', inputType: 'multiInput',
	},
	{
		key: 0, name: 'attachmentdomainexceptions', templateId: 0, section: 'DomainExceptions', inputType: 'multiInput',
	},
	{
		key: 31,
		name: 'attachmentdomainexceptionsexternalpopup',
		templateId: 0,
		section: 'DomainExceptions',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},

	// ── Attachments ──────────────────────────────────────
	{
		key: 3,
		name: 'attachmentmode',
		templateId: 0,
		section: 'Attachments',
		inputType: 'select',
		options: [
			{ value: 'MaximumAttachmentSize', label: 'Trigger on maximum attachment size' },
			{ value: 'Ask', label: 'Ask every time' },
			{ value: 'Off', label: 'None' },
		],
	},
	{
		key: 4,
		name: 'attachmentsize',
		templateId: 0,
		section: 'Attachments',
		inputType: 'numeric',
		visibilityRule: { dependsOn: 'attachmentmode', showWhen: 'MaximumAttachmentSize' },
	},
	{
		key: 5, name: 'senderexceptions', templateId: 0, section: 'Attachments', inputType: 'multiInput',
	},

	// ── File Handling ────────────────────────────────────
	{
		key: 8, name: 'pathuploadfiles', templateId: 0, section: 'FileHandling', inputType: 'text',
	},
	{
		key: 10, name: 'sharefilehtml', templateId: 0, section: 'FileHandling', inputType: 'textarea',
	},
	{
		key: 7, name: 'pathpublicshare', templateId: 0, section: 'FileHandling', inputType: 'text',
	},
	{
		key: 9, name: 'sharefolderhtml', templateId: 0, section: 'FileHandling', inputType: 'textarea',
	},
	{
		key: 700,
		name: 'enforceFiledrop',
		templateId: 0,
		section: 'FileHandling',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},

	// ── Secure Mail ──────────────────────────────────────
	{
		key: 11,
		name: 'securemail',
		templateId: 0,
		section: 'SecureMail',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 25,
		name: 'securemailenforced',
		templateId: 0,
		section: 'SecureMail',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 600,
		name: 'securemailuimode',
		templateId: 0,
		section: 'SecureMail',
		inputType: 'select',
		options: [
			{ value: 'toolbar', label: 'Toolbar in message compose window' },
			{ value: 'button', label: 'Button in ribbon' },
		],
	},
	{
		key: 24, name: 'pathsecuremailbox', templateId: 0, section: 'SecureMail', inputType: 'text',
	},
	{
		key: 12, name: 'securemailhtml', templateId: 0, section: 'SecureMail', inputType: 'textarea',
	},

	// ── Guest Accounts ───────────────────────────────────
	{
		key: 27,
		name: 'guestaccountsenabled',
		templateId: 0,
		section: 'GuestAccounts',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 26,
		name: 'guestaccountsenforced',
		templateId: 0,
		section: 'GuestAccounts',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 301,
		name: 'disableanonymousshare',
		templateId: 0,
		section: 'GuestAccounts',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 302, name: 'htmlsnippetguestaccounts', templateId: 0, section: 'GuestAccounts', inputType: 'textarea',
	},
	{
		key: 303, name: 'htmlsnippetpublicaccounts', templateId: 0, section: 'GuestAccounts', inputType: 'textarea',
	},

	// ── Advanced Theming (templateId: 1) ─────────────────
	{
		key: 80,
		name: 'AdvancedThemingEnabled',
		templateId: 1,
		section: 'AdvancedTheming',
		inputType: 'select',
		options: [
			{ value: 'true', label: 'Enabled' },
			{ value: 'false', label: 'Disabled' },
		],
	},
	{
		key: 104, name: 'VendorName', templateId: 1, section: 'AdvancedTheming', inputType: 'text',
	},
	{ key: 85, name: 'ButtonPrimaryColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 86, name: 'ButtonPrimaryFontColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 87, name: 'ButtonPrimaryHoverColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 97, name: 'ButtonPrimaryIconColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 88, name: 'ButtonSecondaryColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 90, name: 'ButtonSecondaryFontColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 89, name: 'ButtonSecondaryHoverColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 98, name: 'ButtonSecondaryIconColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 100, name: 'DialogFooterBackgroundColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 101, name: 'DialogFooterFontColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 102, name: 'DialogFooterHoverColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 103, name: 'DialogFooterIconColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 82, name: 'DialogFooterIconBackgroundColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 84, name: 'DialogHeaderColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 95, name: 'DialogHeaderFontColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 92, name: 'PopupBackgroundColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 93, name: 'GeneralFontColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 81, name: 'GeneralIconColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 83, name: 'TaskpaneActivityTrackerColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 94, name: 'TaskpaneActivityTrackerFontColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 91, name: 'TaskpaneSecureMailColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 96, name: 'TaskpaneSecureMailFontColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },
	{ key: 99, name: 'TaskpaneSecureMailControlColor', templateId: 1, section: 'AdvancedTheming', inputType: 'color', visibilityRule: { dependsOn: 'AdvancedThemingEnabled', showWhen: 'true' } },

	// ── Teams (templateId: 2) ────────────────────────────
	{
		key: 501, name: 'teams_pathuploadfiles', templateId: 2, section: 'Teams', inputType: 'text',
	},
]

/**
 * Lookup a setting definition by its numeric key (settingkeyid)
 * @param key
 */
export function getSettingByKey(key: number): SettingDefinition | undefined {
	return settingsRegistry.find(s => s.key === key)
}

/**
 * Lookup a setting definition by its string name (settingkeyname)
 * @param name
 */
export function getSettingByName(name: string): SettingDefinition | undefined {
	return settingsRegistry.find(s => s.name === name)
}

/**
 * Get all settings for a given section
 * @param section
 */
export function getSettingsForSection(section: string): SettingDefinition[] {
	return settingsRegistry.filter(s => s.section === section)
}

/**
 * Get all settings for a given templateId
 * @param templateId
 */
export function getSettingsForTemplate(templateId: number): SettingDefinition[] {
	return settingsRegistry.filter(s => s.templateId === templateId)
}
