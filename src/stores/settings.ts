import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { SettingValue, SettingDefinition } from '../types/settings'
import { settingsRegistry } from '../common/settingsRegistry'
import * as api from '../services/settingsApi'

export const useSettingsStore = defineStore('settings', () => {
	/** Current setting values keyed by settingkeyid */
	const values = ref<Map<number, SettingValue>>(new Map())
	/** Which setting keys are currently being saved */
	const savingKeys = ref<Set<number>>(new Set())
	/** Which setting keys were recently saved (for success indicator) */
	const savedKeys = ref<Set<number>>(new Set())
	/** Loading state */
	const loading = ref(false)
	/** Currently selected group ID ('' = default) */
	const currentGroupId = ref('')

	/**
	 * Get a setting value by its key id
	 * @param keyId
	 */
	function getValue(keyId: number): string {
		return values.value.get(keyId)?.value ?? ''
	}

	/**
	 * Check if a setting is inherited (no group-specific override)
	 * @param keyId
	 */
	function isInherited(keyId: number): boolean {
		const val = values.value.get(keyId)
		if (!val) return true
		// Default group settings are never "inherited"
		if (currentGroupId.value === '') return false
		// If groupid is empty or '0' or 'default', it's the default value
		return !val.groupid || val.groupid === '0' || val.groupid === 'default'
	}

	/**
	 * Check if a setting is currently saving
	 * @param keyId
	 */
	function isSaving(keyId: number): boolean {
		return savingKeys.value.has(keyId)
	}

	/**
	 * Check if a setting was recently saved
	 * @param keyId
	 */
	function wasSaved(keyId: number): boolean {
		return savedKeys.value.has(keyId)
	}

	/**
	 * Load all settings for a group
	 * @param groupId
	 */
	async function loadSettings(groupId: string) {
		loading.value = true
		currentGroupId.value = groupId
		try {
			const data = groupId === ''
				? await api.fetchSettingsForDefaultGroup()
				: await api.fetchSettingsForGroup(groupId)

			values.value = new Map()
			for (const item of data) {
				values.value.set(item.settingkeyid, item)
			}
		} finally {
			loading.value = false
		}
	}

	/**
	 * Save a single setting value
	 * @param keyId
	 * @param value
	 */
	async function saveSetting(keyId: number, value: string) {
		const groupId = currentGroupId.value
		savingKeys.value.add(keyId)

		try {
			// Try update first (optimistic)
			let result: SettingValue
			try {
				result = await api.updateSettingValue(keyId, value, groupId, groupId || undefined)
			} catch {
				// Falls back to create if update fails
				result = await api.createSettingValue(keyId, value, groupId, groupId || undefined)
			}

			values.value.set(keyId, result)

			// Show success indicator briefly
			savedKeys.value.add(keyId)
			setTimeout(() => savedKeys.value.delete(keyId), 1500)
		} finally {
			savingKeys.value.delete(keyId)
		}
	}

	/**
	 * Delete a group-specific override (revert to inherited default)
	 * @param keyId
	 */
	async function inheritSetting(keyId: number) {
		const groupId = currentGroupId.value
		if (!groupId) return

		savingKeys.value.add(keyId)
		try {
			const result = await api.deleteSettingValue(keyId, groupId)
			values.value.set(keyId, result)
		} finally {
			savingKeys.value.delete(keyId)
		}
	}

	/**
	 * Create a group-specific override from the current inherited value
	 * @param keyId
	 */
	async function overrideSetting(keyId: number) {
		const groupId = currentGroupId.value
		if (!groupId) return

		const currentValue = getValue(keyId)
		savingKeys.value.add(keyId)
		try {
			const result = await api.createSettingValue(keyId, currentValue, groupId, groupId)
			values.value.set(keyId, result)
		} finally {
			savingKeys.value.delete(keyId)
		}
	}

	/**
	 * Check if a setting should be visible based on visibility rules
	 * @param def
	 */
	function isVisible(def: SettingDefinition): boolean {
		if (!def.visibilityRule) return true
		const depDef = settingsRegistry.find(s => s.name === def.visibilityRule!.dependsOn)
		if (!depDef) return true
		return getValue(depDef.key) === def.visibilityRule.showWhen
	}

	return {
		values,
		savingKeys,
		savedKeys,
		loading,
		currentGroupId,
		getValue,
		isInherited,
		isSaving,
		wasSaved,
		loadSettings,
		saveSetting,
		inheritSetting,
		overrideSetting,
		isVisible,
	}
})
