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
	<div class="groups-management">
		<div class="groups-management__panel">
			<h3>Inactive Groups</h3>
			<input v-model="ncFilter"
				type="text"
				placeholder="Filter groups..."
				class="groups-management__filter">
			<div class="groups-management__list">
				<div v-for="group in filteredNcGroups"
					:key="group.gid"
					class="groups-management__item"
					@click="onAddGroup(group.gid)">
					<span>{{ group.displayName }}</span>
					<span class="groups-management__add-icon" title="Add to Sendent groups">&#x2192;</span>
				</div>
				<div v-if="filteredNcGroups.length === 0" class="groups-management__empty">
					No groups found
				</div>
			</div>
		</div>

		<div class="groups-management__panel">
			<h3>Active Groups</h3>
			<input v-model="sendentFilter"
				type="text"
				placeholder="Filter groups..."
				class="groups-management__filter">
			<div ref="sortableRef"
				class="groups-management__list groups-management__list--sortable">
				<!-- Default group (always first, not sortable) -->
				<div class="groups-management__item groups-management__item--default"
					:class="{ 'groups-management__item--selected': groupsStore.selectedGroupId === '' }"
					@click="onSelectGroup('')">
					<span>Default</span>
				</div>
				<div v-for="group in filteredSendentGroups"
					:key="group.gid"
					:data-gid="group.gid"
					class="groups-management__item groups-management__item--sendent"
					:class="{
						'groups-management__item--selected': groupsStore.selectedGroupId === group.gid,
						'groups-management__item--deleted': group.displayName.includes('*** DELETED GROUP ***'),
					}"
					@click="onSelectGroup(group.gid)">
					<span class="groups-management__drag-handle" title="Drag to reorder">&#x2630;</span>
					<span class="groups-management__name">{{ group.displayName }}</span>
					<button class="groups-management__remove"
						title="Remove from Sendent groups"
						@click.stop="onRemoveGroup(group.gid)">
						X
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useGroupsStore } from '../../stores/groups'
import { useSettingsStore } from '../../stores/settings'
import { useLicenseStore } from '../../stores/license'
import { useSortable } from '../../composables/useSortable'

const groupsStore = useGroupsStore()
const settingsStore = useSettingsStore()
const licenseStore = useLicenseStore()

const ncFilter = ref('')
const sendentFilter = ref('')
const sortableRef = ref<HTMLElement | null>(null)

const filteredNcGroups = computed(() => {
	const filter = ncFilter.value.toLowerCase()
	if (!filter) return groupsStore.ncGroups
	return groupsStore.ncGroups.filter(g =>
		g.displayName.toLowerCase().includes(filter) || g.gid.toLowerCase().includes(filter),
	)
})

const filteredSendentGroups = computed(() => {
	const filter = sendentFilter.value.toLowerCase()
	if (!filter) return groupsStore.sendentGroups
	return groupsStore.sendentGroups.filter(g =>
		g.displayName.toLowerCase().includes(filter) || g.gid.toLowerCase().includes(filter),
	)
})

useSortable(sortableRef, {
	handle: '.groups-management__drag-handle',
	onEnd(oldIndex, newIndex) {
		// Adjust for the Default group item (index 0 in DOM, not in array)
		const adjusted = [...groupsStore.sendentGroups]
		const [moved] = adjusted.splice(oldIndex - 1, 1)
		adjusted.splice(newIndex - 1, 0, moved)
		groupsStore.reorderGroups(adjusted)
	},
})

/**
 *
 * @param gid
 */
function onAddGroup(gid: string) {
	groupsStore.addGroup(gid)
}

/**
 *
 * @param gid
 */
function onRemoveGroup(gid: string) {
	groupsStore.removeGroup(gid)
}

/**
 *
 * @param gid
 */
async function onSelectGroup(gid: string) {
	groupsStore.selectGroup(gid)
	await Promise.all([
		settingsStore.loadSettings(gid),
		licenseStore.refreshStatus(gid),
	])
}

onMounted(() => {
	// Load settings for default group initially
	onSelectGroup('')
})
</script>

<style scoped>
.groups-management {
	display: flex;
	gap: 20px;
	margin-bottom: 24px;
}

.groups-management__panel {
	flex: 1;
	min-width: 250px;
	max-width: 400px;
}

.groups-management__panel h3 {
	font-size: 14px;
	font-weight: 600;
	margin-bottom: 8px;
}

.groups-management__filter {
	width: 100%;
	margin-bottom: 8px;
}

.groups-management__list {
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	height: 300px;
	overflow-y: auto;
}

.groups-management__item {
	display: flex;
	align-items: center;
	padding: 8px 12px;
	cursor: pointer;
	border-bottom: 1px solid var(--color-border-dark);
	gap: 8px;
}

.groups-management__item:last-child {
	border-bottom: none;
}

.groups-management__item:hover {
	background: var(--color-background-hover);
}

.groups-management__item--selected {
	background: var(--color-primary-element-light) !important;
}

.groups-management__item--deleted {
	opacity: 0.5;
	text-decoration: line-through;
}

.groups-management__item--default {
	font-weight: 600;
}

.groups-management__drag-handle {
	cursor: grab;
	color: var(--color-text-maxcontrast);
	font-size: 12px;
}

.groups-management__name {
	flex: 1;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.groups-management__add-icon {
	color: var(--color-text-maxcontrast);
}

.groups-management__remove {
	background: none;
	border: none;
	cursor: pointer;
	color: var(--color-error-text);
	padding: 2px 6px;
	font-variant-emoji: text;
}

.groups-management__empty {
	padding: 12px;
	text-align: center;
	color: var(--color-text-maxcontrast);
	font-style: italic;
}
</style>
