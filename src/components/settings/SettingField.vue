<!--
  - @copyright Copyright (c) 2026 Sendent B.V.
  -
  - @author Sendent B.V. <info@sendent.com>
  -
  - @license AGPL-3.0-or-later
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -->
<template>
	<div v-if="visible"
		class="setting-field"
		:class="{ 'setting-field--block': isBlockInput, 'setting-field--annotated': hasHint || exceedsNextcloudMaxShareDays }">
		<div class="setting-field__header">
			<label class="setting-field__label">{{ label }}</label>
			<SaveIndicator :saving="saving" :saved="saved" />
		</div>
		<div class="setting-field__input">
			<select v-if="definition.inputType === 'select'"
				v-model="localValue"
				:disabled="disabled"
				@change="save">
				<option v-for="opt in definition.options"
					:key="opt.value"
					:value="opt.value">
					{{ opt.label }}
				</option>
			</select>
			<input v-else-if="definition.inputType === 'text'"
				v-model="localValue"
				type="text"
				:disabled="disabled"
				@change="save">
			<input v-else-if="definition.inputType === 'numeric'"
				v-model="localValue"
				type="number"
				:disabled="disabled"
				:min="numericMin"
				@change="onNumericChange">
			<input v-else-if="definition.inputType === 'color'"
				v-model="localValue"
				type="color"
				:disabled="disabled"
				class="setting-field__color"
				@change="save">
			<MultiInputList v-else-if="definition.inputType === 'multiInput'"
				v-model="localValue"
				:disabled="disabled"
				@update:model-value="save" />
			<SettingTextarea v-else-if="definition.inputType === 'textarea'"
				:model-value="localValue"
				:disabled="disabled"
				:template-variables="definition.templateVariables"
				:signature-mode="definition.signatureMode"
				@save="onTextareaSave"
				@reset="onTextareaReset" />
			<p v-if="hasHint" class="setting-field__hint">
				<span v-if="definition.classicOnly" class="setting-field__tag">{{ t('sendent', 'Outlook Classic only') }}</span>
				{{ hint }}
			</p>
			<p v-if="exceedsNextcloudMaxShareDays" class="setting-field__warning">
				{{ n('sendent',
					'Nextcloud limits shared links to %n day, so shares created from the add-ins will expire after %n day.',
					'Nextcloud limits shared links to %n days, so shares created from the add-ins will expire after %n days.',
					nextcloudMaxShareDays) }}
			</p>
		</div>
		<InheritanceCheckbox :inherited="inherited"
			:show-checkbox="isGroupSelected"
			@toggle="toggleInheritance" />
	</div>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import type { SettingDefinition } from '../../types/settings'
import { useSettingField } from '../../composables/useSettingField'
import { useSettingsStore } from '../../stores/settings'
import { useDependenciesStore } from '../../stores/dependencies'
import SaveIndicator from './SaveIndicator.vue'
import InheritanceCheckbox from './InheritanceCheckbox.vue'
import MultiInputList from './MultiInputList.vue'
import SettingTextarea from './SettingTextarea.vue'

const props = defineProps<{
	definition: SettingDefinition
	label: string
}>()

const {
	localValue,
	inherited,
	saving,
	saved,
	visible,
	isGroupSelected,
	disabled,
	save,
	toggleInheritance,
} = useSettingField(props.definition)

const { shareExpirationDays, shareExpirationEnforced } = storeToRefs(useDependenciesStore())

const isBlockInput = computed(() =>
	props.definition.inputType === 'textarea' || props.definition.inputType === 'multiInput',
)

/** The selected option's hint when it has one, otherwise the setting's own. */
const hint = computed(() =>
	props.definition.options?.find(opt => opt.value === localValue.value)?.description
	?? props.definition.description,
)

const hasHint = computed(() => Boolean(hint.value) || props.definition.classicOnly === true)

/** Maximum share days enforced by Nextcloud; the add-ins cap the Sendent value at it. */
const nextcloudMaxShareDays = computed(() => {
	if (props.definition.name === 'sharedays'
		&& shareExpirationEnforced.value
		&& shareExpirationDays.value > 0) {
		return shareExpirationDays.value
	}
	return undefined
})

const exceedsNextcloudMaxShareDays = computed(() =>
	nextcloudMaxShareDays.value !== undefined && Number(localValue.value) > nextcloudMaxShareDays.value,
)

const numericMin = computed(() => {
	if (props.definition.name === 'sharedays') {
		return 1
	}
	return undefined
})

function onNumericChange() {
	const val = Number(localValue.value)
	if (numericMin.value && val < numericMin.value) {
		localValue.value = String(numericMin.value)
	}
	save()
}

// When sharedaysenabled is toggled to Disabled, set sharedays to -1
const store = useSettingsStore()
watch(localValue, (newVal) => {
	if (props.definition.name === 'sharedaysenabled' && newVal === 'False') {
		store.saveSetting(32, '-1')
	}

	// When a feature is disabled, also disable its "enforced" sub-setting
	if (props.definition.name === 'securemail' && newVal === 'False'
		&& store.getValue(25) !== 'False') {
		store.saveSetting(25, 'False') // securemailenforced
	}
	if (props.definition.name === 'guestaccountsenabled' && newVal === 'False'
		&& store.getValue(26) !== 'False') {
		store.saveSetting(26, 'False') // guestaccountsenforced
	}
})

function onTextareaSave(content: string) {
	localValue.value = content
	save()
}

/**
 *
 */
async function onTextareaReset() {
	await store.resetSetting(props.definition.key)
}
</script>

<style scoped>
.setting-field {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 12px;
}

.setting-field__header {
	display: flex;
	align-items: center;
	min-width: 250px;
	flex-shrink: 0;
}

.setting-field__label {
	font-weight: 500;
	font-size: 14px;
}

.setting-field__input {
	flex: 0 0 400px;
	min-width: 0;
}

.setting-field__input select,
.setting-field__input input[type="text"],
.setting-field__input input[type="number"] {
	width: 100%;
}

/* Keep the label level with the input text instead of centering it against input + hint or warning */
.setting-field--annotated {
	align-items: baseline;
}

.setting-field__hint {
	margin: 4px 0 0;
	font-size: 13px;
	line-height: 1.4;
	color: var(--color-text-maxcontrast);
}

.setting-field__tag {
	display: inline-block;
	margin-right: 4px;
	padding: 0 8px;
	border: 1px solid var(--color-border-dark);
	border-radius: var(--border-radius-pill);
	font-size: 12px;
	white-space: nowrap;
}

.setting-field__warning {
	margin: 8px 0 0;
	padding: 8px 12px;
	border-left: 4px solid var(--color-warning);
	border-radius: var(--border-radius);
	background-color: var(--color-background-hover);
}

.setting-field__color {
	width: 60px;
	height: 34px;
	padding: 2px;
	cursor: pointer;
}

.setting-field__input select:disabled,
.setting-field__input input:disabled,
.setting-field__input textarea:disabled {
	opacity: 0.6;
	cursor: not-allowed;
}

/* Block layout for textarea and multiInput — keep same column width for alignment */
.setting-field--block .setting-field__input {
	flex: 0 0 400px;
}
</style>
