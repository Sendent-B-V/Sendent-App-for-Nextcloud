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
