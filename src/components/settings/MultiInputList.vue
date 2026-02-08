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
				X
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
