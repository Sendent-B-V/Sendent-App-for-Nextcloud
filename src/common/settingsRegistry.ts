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
import { translate as t } from '@nextcloud/l10n'
import type { SettingDefinition } from '../types/settings'

/** Placeholder tags resolved from the Nextcloud user profile (see SignatureService.php). */
export const SIGNATURE_TEMPLATE_VARIABLES = [
	'{DISPLAYNAME}',
	'{FIRSTNAME}',
	'{MIDDLENAME}',
	'{LASTNAME}',
	'{PRONOUNS}',
	'{EMAIL}',
	'{PHONE}',
	'{LOCATION}',
	'{WEBSITE}',
	'{ORGANISATION}',
	'{ROLE}',
	'{HEADLINE}',
	'{BIOGRAPHY}',
	'{LOGO}',
]

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
		classicOnly: true,
		description: t('sendent', 'Language of the add-in when a user signs in for the first time. Users can change it afterwards.'),
		options: [
			{ value: 'en', label: t('sendent', 'English') },
			{ value: 'nl', label: t('sendent', 'Nederlands') },
			{ value: 'fr', label: t('sendent', 'Français') },
			{ value: 'de', label: t('sendent', 'Deutsch') },
			{ value: 'it', label: t('sendent', 'Italiano') },
			{ value: 'es', label: t('sendent', 'Español') },
			{ value: 'da', label: t('sendent', 'Dansk') },
			{ value: 'ru', label: t('sendent', 'Russkiy') },
		],
	},
	{
		key: 28,
		name: 'insertatcursor',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		description: t('sendent', 'Where the add-in puts its snippet, the block with the share link, in the email.'),
		options: [
			{
				value: 'True',
				label: t('sendent', 'At cursor'),
				description: t('sendent', 'At the cursor. When the cursor is not in the message body, the snippet goes to the top.'),
			},
			{
				value: 'False',
				label: t('sendent', 'Top of email body'),
				description: t('sendent', 'Above the existing message text.'),
			},
		],
	},
	{
		key: 6,
		name: 'dateaddition',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		description: t('sendent', 'Every share gets its own folder inside the upload folder. This setting adds a folder named after the upload date in between.'),
		options: [
			{ value: 'True', label: t('sendent', 'Add upload date to folder path') },
			{ value: 'False', label: t('sendent', 'Do not add upload date to folder path') },
		],
	},
	{
		key: 22,
		name: 'debugmode',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		classicOnly: true,
		description: t('sendent', "Writes a log file to the user's Documents folder, for troubleshooting. It is applied when a user first signs in; users can change it afterwards."),
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},
	{
		key: 17,
		name: 'disablesettings',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		description: t('sendent', 'Whether users can see the settings page inside the add-in.'),
		options: [
			{
				value: 'False',
				label: t('sendent', 'Enabled'),
				description: t('sendent', 'Users can open the settings of the add-in.'),
			},
			{
				value: 'True',
				label: t('sendent', 'Disabled'),
				description: t('sendent', 'The settings are hidden from users.'),
			},
		],
	},
	{
		key: 19,
		name: 'passwordcontrolbehavior',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		description: t('sendent', 'When the add-in asks the user for a password to protect a share. Not used when “Disable anonymous share” is on.'),
		options: [
			{
				value: 'Off',
				label: t('sendent', 'Off'),
				description: t('sendent', 'The add-in does not ask for a password.'),
			},
			{
				value: 'BeforeSend',
				label: t('sendent', 'Before sending'),
				description: t('sendent', 'When the user starts sharing.'),
			},
			{
				value: 'BeforeSendAndAfter',
				label: t('sendent', 'Before and after sending'),
				description: t('sendent', 'When the user starts sharing. After the upload, the password is shown again so it can be copied.'),
			},
		],
	},
	{
		key: 23,
		name: 'sendmode',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		description: t('sendent', 'How the share password reaches the recipient. Not used when “Disable anonymous share” is on.'),
		options: [
			{
				value: 'CurrentMail',
				label: t('sendent', 'Include in email body'),
				description: t('sendent', 'In the same email as the share link.'),
			},
			{
				value: 'Separate',
				label: t('sendent', 'Send in separate email'),
				description: t('sendent', 'In a second email. The add-in opens it as a draft and the user sends it.'),
			},
			{ value: 'External', label: t('sendent', 'Use external service (like sms-gateway)') },
		],
	},
	{
		key: 30,
		name: 'htmlsnippetpassword',
		templateId: 0,
		section: 'General',
		inputType: 'textarea',
		description: t('sendent', 'The text of the separate password email.'),
		visibilityRule: { dependsOn: 'sendmode', showWhen: 'Separate' },
	},
	{
		key: 401,
		name: 'statussync',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		classicOnly: true,
		description: t('sendent', "Keeps the user's Nextcloud status in line with Outlook: do not disturb during meetings, away when the user is idle, otherwise online."),
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},
	{
		key: 402, name: 'presencedomains', templateId: 0, section: 'General', inputType: 'multiInput',
	},
	{
		key: 33,
		name: 'sharedaysenabled',
		templateId: 0,
		section: 'General',
		inputType: 'select',
		defaultValue: 'False',
		description: t('sendent', 'Sets a default expiration for shares made from the add-in.'),
		options: [
			{
				value: 'True',
				label: t('sendent', 'Enabled'),
				description: t('sendent', 'Shares expire after the number of days set below.'),
			},
			{
				value: 'False',
				label: t('sendent', 'Disabled'),
				description: t('sendent', 'The default expiration set in Nextcloud applies, if there is one.'),
			},
		],
	},
	{
		key: 32,
		name: 'sharedays',
		templateId: 0,
		section: 'General',
		inputType: 'numeric',
		defaultValue: '7',
		description: t('sendent', 'Number of days after which a new share expires. If Nextcloud enforces a shorter maximum, that maximum applies.'),
		visibilityRule: { dependsOn: 'sharedaysenabled', showWhen: 'True' },
	},

	// ── Talk ─────────────────────────────────────────────
	{
		key: 202,
		name: 'talkenabled',
		templateId: 0,
		section: 'Talk',
		inputType: 'select',
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},
	{
		key: 201,
		name: 'generatetalkpassword',
		templateId: 0,
		section: 'Talk',
		inputType: 'select',
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
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
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
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
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
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
			{ value: '1', label: t('sendent', 'Upload Attachments') },
			{ value: '2', label: t('sendent', 'Upload Attachments and original email') },
			{ value: '3', label: t('sendent', 'Upload Attachments, original email and use Secure Mail') },
		],
	},
	{
		key: 207,
		name: 'archivecreatefolder',
		templateId: 0,
		section: 'Archiving',
		inputType: 'select',
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},

	// ── Domain Exceptions ────────────────────────────────
	{
		key: 2,
		name: 'attachmentdomainexceptionsinternal',
		templateId: 0,
		section: 'DomainExceptions',
		inputType: 'multiInput',
		description: t('sendent', 'Recipients on this list are exempt from the attachment and Secure Mail checks. An email is only exempt when every recipient is on this list or the external one.'),
		format: t('sendent', 'Each entry is a domain (example.com) or a full address (name@example.com). Matches are exact, so subdomains are not included.'),
	},
	{
		key: 0,
		name: 'attachmentdomainexceptions',
		templateId: 0,
		section: 'DomainExceptions',
		inputType: 'multiInput',
		description: t('sendent', 'Works like the internal list. The difference is that the add-in can ask the user to confirm before sending to these recipients, see the popup setting.'),
		format: t('sendent', 'Each entry is a domain (example.com) or a full address (name@example.com). Matches are exact, so subdomains are not included.'),
	},
	{
		key: 31,
		name: 'attachmentdomainexceptionsexternalpopup',
		templateId: 0,
		section: 'DomainExceptions',
		inputType: 'select',
		description: t('sendent', 'Asks the user to confirm before an exempt email is sent to recipients on the external list.'),
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
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
			{ value: 'MaximumAttachmentSize', label: t('sendent', 'Trigger on maximum attachment size') },
			{ value: 'Ask', label: t('sendent', 'Ask every time') },
			{ value: 'Off', label: t('sendent', 'None') },
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
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},

	// ── Secure Mail ──────────────────────────────────────
	{
		key: 11,
		name: 'securemail',
		templateId: 0,
		section: 'SecureMail',
		inputType: 'select',
		description: t('sendent', 'Lets users secure an email: the body is uploaded to Nextcloud and the recipient gets a link instead.'),
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},
	{
		key: 25,
		name: 'securemailenforced',
		templateId: 0,
		section: 'SecureMail',
		inputType: 'select',
		description: t('sendent', 'Emails cannot be sent until they are secured and have no attachments left. Emails covered by the domain or sender exceptions are exempt.'),
		visibilityRule: { dependsOn: 'securemail', showWhen: 'True' },
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},
	{
		key: 600,
		name: 'securemailuimode',
		templateId: 0,
		section: 'SecureMail',
		inputType: 'select',
		classicOnly: true,
		description: t('sendent', 'Where users find Secure Mail in Outlook.'),
		visibilityRule: { dependsOn: 'securemail', showWhen: 'True' },
		options: [
			{ value: 'toolbar', label: t('sendent', 'Toolbar in message compose window') },
			{ value: 'button', label: t('sendent', 'Button in ribbon') },
		],
	},
	{
		key: 24,
		name: 'pathsecuremailbox',
		templateId: 0,
		section: 'SecureMail',
		inputType: 'text',
		description: t('sendent', "Folder in each user's Nextcloud files where secured emails are stored."),
		format: t('sendent', 'A path such as Outlook/SecureMail-Share. That folder is used when the field is empty.'),
		visibilityRule: { dependsOn: 'securemail', showWhen: 'True' },
	},
	{
		key: 12,
		name: 'securemailhtml',
		templateId: 0,
		section: 'SecureMail',
		inputType: 'textarea',
		description: t('sendent', 'The text that replaces the body of a secured email.'),
		visibilityRule: { dependsOn: 'securemail', showWhen: 'True' },
	},

	// ── Guest Accounts ───────────────────────────────────
	{
		key: 27,
		name: 'guestaccountsenabled',
		templateId: 0,
		section: 'GuestAccounts',
		inputType: 'select',
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},
	{
		key: 26,
		name: 'guestaccountsenforced',
		templateId: 0,
		section: 'GuestAccounts',
		inputType: 'select',
		visibilityRule: { dependsOn: 'guestaccountsenabled', showWhen: 'True' },
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},
	{
		key: 301,
		name: 'disableanonymousshare',
		templateId: 0,
		section: 'GuestAccounts',
		inputType: 'select',
		visibilityRule: { dependsOn: 'guestaccountsenabled', showWhen: 'True' },
		options: [
			{ value: 'True', label: t('sendent', 'Enabled') },
			{ value: 'False', label: t('sendent', 'Disabled') },
		],
	},
	{
		key: 302,
		name: 'htmlsnippetguestaccounts',
		templateId: 0,
		section: 'GuestAccounts',
		clients: ['outlook'],
		inputType: 'textarea',
		visibilityRule: { dependsOn: 'guestaccountsenabled', showWhen: 'True' },
	},
	{
		key: 303,
		name: 'htmlsnippetpublicaccounts',
		templateId: 0,
		section: 'GuestAccounts',
		clients: ['outlook'],
		inputType: 'textarea',
		visibilityRule: { dependsOn: 'guestaccountsenabled', showWhen: 'True' },
	},

	// ── EmailSignature ───────────────────────────────────
	{
		key: 800,
		name: 'enablesignaturepush',
		templateId: 0,
		section: 'EmailSignature',
		inputType: 'select',
		options: [
			{ value: 'True', label: 'Enabled' },
			{ value: 'False', label: 'Disabled' },
		],
	},
	{
		key: 801,
		name: 'signaturehtml',
		templateId: 0,
		section: 'EmailSignature',
		inputType: 'textarea',
		visibilityRule: { dependsOn: 'enablesignaturepush', showWhen: 'True' },
		templateVariables: SIGNATURE_TEMPLATE_VARIABLES,
		signatureMode: true,
	},

	// ── Advanced Theming (templateId: 1) ─────────────────
	{
		key: 80,
		name: 'AdvancedThemingEnabled',
		templateId: 1,
		section: 'AdvancedTheming',
		inputType: 'select',
		options: [
			{ value: 'true', label: t('sendent', 'Enabled') },
			{ value: 'false', label: t('sendent', 'Disabled') },
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
	{
		key: 502,
		name: 'teams_openfilemode',
		templateId: 2,
		section: 'Teams',
		inputType: 'select',
		options: [
			{ value: 'Off', label: t('sendent', 'Off') },
			{ value: 'webdav', label: t('sendent', 'Via WebDAV') },
			{ value: 'desktop', label: t('sendent', 'Via Nextcloud Desktop Client') },
		],
	},
]

/**
 * Lookup a setting definition by its numeric key (settingkeyid)
 * @param key - The numeric settingkeyid to look up
 */
export function getSettingByKey(key: number): SettingDefinition | undefined {
	return settingsRegistry.find(s => s.key === key)
}

/**
 * Lookup a setting definition by its string name (settingkeyname)
 * @param name - The settingkeyname to look up
 */
export function getSettingByName(name: string): SettingDefinition | undefined {
	return settingsRegistry.find(s => s.name === name)
}

/**
 * Get all settings for a given section
 * @param section - The section identifier to filter settings by
 */
export function getSettingsForSection(section: string): SettingDefinition[] {
	return settingsRegistry.filter(s => s.section === section)
}

/**
 * Get all settings for a given templateId
 * @param templateId - The numeric template id to filter settings by
 */
export function getSettingsForTemplate(templateId: number): SettingDefinition[] {
	return settingsRegistry.filter(s => s.templateId === templateId)
}
