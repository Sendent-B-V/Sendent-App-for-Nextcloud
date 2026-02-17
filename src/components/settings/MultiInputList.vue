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
	<div class="multi-input-list">
		<div v-for="(item, index) in items"
			:key="index"
			class="multi-input-list__row">
			<input :value="item"
				type="text"
				:disabled="disabled"
				:placeholder="index === items.length - 1 ? 'Add new...' : ''"
				@input="onItemInput(index, ($event.target as HTMLInputElement).value)"
				@change="emitValue">
			<button v-if="item && !disabled"
				class="multi-input-list__delete"
				type="button"
				title="Remove"
				@click="removeItem(index)">
				×
			</button>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
	modelValue: string
	disabled?: boolean
}>()

const emit = defineEmits<{
	'update:modelValue': [value: string]
}>()

const items = ref<string[]>([])

// Parse semicolon-separated string into array
/**
 *
 * @param val
 */
function parseValue(val: string) {
	const parts = val ? val.split(';').map(s => s.trim()).filter(Boolean) : []
	// Always have an empty slot at the end for adding
	items.value = [...parts, '']
}

watch(() => props.modelValue, parseValue, { immediate: true })

/**
 *
 * @param index
 * @param value
 */
function onItemInput(index: number, value: string) {
	items.value[index] = value
	// If typing in the last (empty) slot, add a new empty slot
	if (index === items.value.length - 1 && value) {
		items.value.push('')
	}
}

/**
 *
 * @param index
 */
function removeItem(index: number) {
	items.value.splice(index, 1)
	emitValue()
}

/**
 *
 */
function emitValue() {
	const value = items.value.filter(Boolean).join(';')
	emit('update:modelValue', value)
}
</script>

<style scoped>
.multi-input-list__row {
	display: flex;
	align-items: center;
	gap: 4px;
	margin-bottom: 4px;
}

.multi-input-list__row input {
	flex: 1;
	max-width: 350px;
}

.multi-input-list__delete {
	background: none;
	border: none;
	cursor: pointer;
	color: var(--color-error-text);
	font-size: 16px;
	padding: 4px 8px;
	font-variant-emoji: text;
}

.multi-input-list__delete:hover {
	background: var(--color-background-hover);
	border-radius: var(--border-radius);
}
</style>
