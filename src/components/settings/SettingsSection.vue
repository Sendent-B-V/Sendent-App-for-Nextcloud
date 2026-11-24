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
	<div class="settings-section">
		<h3 class="settings-section__title"
			:class="{ 'settings-section__title--described': description }">
			{{ title }}
		</h3>
		<p v-if="description" class="settings-section__description">
			{{ description }}
		</p>
		<slot />
		<SettingField v-for="def in definitions"
			:key="def.key"
			:definition="def"
			:label="labels[def.name] ?? def.name" />
	</div>
</template>

<script setup lang="ts">
import type { SettingDefinition } from '../../types/settings'
import SettingField from './SettingField.vue'

defineProps<{
	title: string
	/** One sentence under the title explaining what the feature does. */
	description?: string
	definitions: SettingDefinition[]
	labels: Record<string, string>
}>()
</script>

<style scoped>
.settings-section {
	margin-bottom: 30px;
}

.settings-section__title {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 16px;
	padding-bottom: 8px;
	border-bottom: 1px solid var(--color-border);
}

.settings-section__title--described {
	margin-bottom: 8px;
}

.settings-section__description {
	max-width: 720px;
	margin: 0 0 16px;
	color: var(--color-text-maxcontrast);
}
</style>
