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
import { computed, ref, watch } from 'vue'
import { useSettingsStore } from '../stores/settings'
import type { SettingDefinition } from '../types/settings'

/**
 * Composable for binding a setting field to the settings store.
 * Handles value binding, auto-save on change, and inheritance.
 * @param definition
 */
export function useSettingField(definition: SettingDefinition) {
	const store = useSettingsStore()
	const localValue = ref('')

	/** The stored value */
	const storedValue = computed(() => store.getValue(definition.key))

	/** Whether this setting is inherited from the default group */
	const inherited = computed(() => store.isInherited(definition.key))

	/** Whether this setting is currently saving */
	const saving = computed(() => store.isSaving(definition.key))

	/** Whether this setting was recently saved */
	const saved = computed(() => store.wasSaved(definition.key))

	/** Whether this setting is visible based on visibility rules */
	const visible = computed(() => store.isVisible(definition))

	/** Whether this is a non-default group */
	const isGroupSelected = computed(() => store.currentGroupId !== '')

	/** Whether the field should be disabled (inherited + group selected) */
	const disabled = computed(() => isGroupSelected.value && inherited.value)

	// Sync local value from store
	watch(storedValue, (val) => {
		localValue.value = val
	}, { immediate: true })

	/** Save the current value */
	async function save() {
		if (localValue.value !== storedValue.value) {
			await store.saveSetting(definition.key, localValue.value)
		}
	}

	/**
	 * Toggle inheritance
	 * @param inherit
	 */
	async function toggleInheritance(inherit: boolean) {
		if (inherit) {
			await store.inheritSetting(definition.key)
		} else {
			await store.overrideSetting(definition.key)
		}
	}

	return {
		localValue,
		storedValue,
		inherited,
		saving,
		saved,
		visible,
		isGroupSelected,
		disabled,
		save,
		toggleInheritance,
	}
}
