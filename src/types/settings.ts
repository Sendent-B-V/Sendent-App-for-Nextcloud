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
export type InputType = 'select' | 'text' | 'textarea' | 'color' | 'multiInput' | 'numeric'

export type Section =
	| 'General'
	| 'Talk'
	| 'Archiving'
	| 'DomainExceptions'
	| 'Attachments'
	| 'FileHandling'
	| 'SecureMail'
	| 'GuestAccounts'
	| 'AdvancedTheming'
	| 'Teams'

export interface SelectOption {
	value: string
	label: string
}

export interface VisibilityRule {
	dependsOn: string
	showWhen: string
}

export interface SettingDefinition {
	key: number
	name: string
	templateId: number
	section: Section
	inputType: InputType
	options?: SelectOption[]
	visibilityRule?: VisibilityRule
}

export interface SettingValue {
	id: number
	settingkeyid: number
	value: string
	groupid: string
	group?: string
}

export interface SettingKey {
	id: number
	name: string
	key: string
	valuetype: string
	templateid: number
}
