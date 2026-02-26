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
	<span v-if="saving" class="save-indicator save-indicator--saving">
		&#x21BB;
	</span>
	<span v-else-if="saved" class="save-indicator save-indicator--saved">
		&#x2713;
	</span>
</template>

<script setup lang="ts">
defineProps<{
	saving: boolean
	saved: boolean
}>()
</script>

<style scoped>
.save-indicator {
	display: inline-block;
	margin-left: 8px;
	font-variant-emoji: text;
}

.save-indicator--saving {
	animation: spin 1s linear infinite;
	color: var(--color-text-maxcontrast);
}

.save-indicator--saved {
	color: var(--color-success-text);
	animation: fadeIn 0.3s ease-in;
}

@keyframes spin {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}

@keyframes fadeIn {
	from { opacity: 0; }
	to { opacity: 1; }
}
</style>
