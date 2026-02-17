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
		:class="{ 'setting-field--block': isBlockInput }">
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
				@change="save">
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
				@save="onTextareaSave"
				@reset="onTextareaReset" />
		</div>
		<InheritanceCheckbox :inherited="inherited"
			:show-checkbox="isGroupSelected"
			@toggle="toggleInheritance" />
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { SettingDefinition } from '../../types/settings'
import { useSettingField } from '../../composables/useSettingField'
import { useSettingsStore } from '../../stores/settings'
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

const isBlockInput = computed(() =>
	props.definition.inputType === 'textarea' || props.definition.inputType === 'multiInput',
)

/**
 *
 * @param content
 */
function onTextareaSave(content: string) {
	localValue.value = content
	save()
}

/**
 *
 */
async function onTextareaReset() {
	const store = useSettingsStore()
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
	flex: 1;
	min-width: 0;
}

.setting-field__input select,
.setting-field__input input[type="text"],
.setting-field__input input[type="number"] {
	width: 100%;
	max-width: 400px;
}

.setting-field__color {
	width: 60px !important;
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

/* Block layout for textarea and multiInput (inherently multi-line) */
.setting-field--block {
	flex-wrap: wrap;
}

.setting-field--block .setting-field__input {
	flex-basis: 100%;
	max-width: 600px;
}
</style>
