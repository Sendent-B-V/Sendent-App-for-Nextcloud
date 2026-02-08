<template>
	<div v-if="showCheckbox" class="inheritance-checkbox">
		<label>
			<input type="checkbox"
				:checked="inherited"
				@change="onToggle">
			Use default group settings
		</label>
	</div>
</template>

<script setup lang="ts">
defineProps<{
	inherited: boolean
	showCheckbox: boolean
}>()

const emit = defineEmits<{
	toggle: [inherit: boolean]
}>()

/**
 *
 * @param event
 */
function onToggle(event: Event) {
	const target = event.target as HTMLInputElement
	emit('toggle', target.checked)
}
</script>

<style scoped>
.inheritance-checkbox {
	flex-shrink: 0;
	white-space: nowrap;
}

.inheritance-checkbox label {
	display: flex;
	align-items: center;
	gap: 6px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	cursor: pointer;
}
</style>
