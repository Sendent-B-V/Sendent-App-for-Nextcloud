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
