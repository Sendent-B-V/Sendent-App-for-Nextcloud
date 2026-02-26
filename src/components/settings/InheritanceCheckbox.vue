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
