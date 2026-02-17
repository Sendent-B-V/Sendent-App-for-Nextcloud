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
	<div class="sendent-tabs">
		<div class="sendent-tabs__nav">
			<button v-for="tab in tabs"
				:key="tab.id"
				class="sendent-tabs__tab"
				:class="{ 'sendent-tabs__tab--active': activeTab === tab.id }"
				@click="activeTab = tab.id">
				{{ tab.label }}
			</button>
		</div>
		<div class="sendent-tabs__content">
			<slot :name="activeTab" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Tab {
	id: string
	label: string
}

const props = defineProps<{
	tabs: Tab[]
	defaultTab?: string
}>()

const activeTab = ref(props.defaultTab ?? props.tabs[0]?.id ?? '')
</script>

<style scoped>
.sendent-tabs__nav {
	display: flex;
	gap: 0;
	border-bottom: 1px solid var(--color-border);
	margin-bottom: 20px;
}

.sendent-tabs__tab {
	padding: 10px 20px;
	border: none;
	background: none;
	cursor: pointer;
	font-size: 14px;
	color: var(--color-text-maxcontrast);
	border-bottom: 2px solid transparent;
	transition: color 0.2s, border-color 0.2s;
}

.sendent-tabs__tab:hover {
	color: var(--color-main-text);
	background: var(--color-background-hover);
}

.sendent-tabs__tab--active {
	color: var(--color-primary);
	border-bottom-color: var(--color-primary);
	background: var(--color-primary-element-light);
}

.sendent-tabs__content {
	padding: 0 4px;
}
</style>
