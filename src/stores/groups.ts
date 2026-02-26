/**
 * @copyright Copyright (c) 2026 Sendent B.V.
 *
 * @author Sendent B.V. <info@sendent.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { loadState } from '@nextcloud/initial-state'
import type { GroupItem } from '../types/groups'
import * as api from '../services/groupsApi'

export const useGroupsStore = defineStore('groups', () => {
	/** All Nextcloud groups not assigned to Sendent */
	const ncGroups = ref<GroupItem[]>([])
	/** Groups assigned to Sendent (ordered) */
	const sendentGroups = ref<GroupItem[]>([])
	/** Currently selected group ID ('' = default group) */
	const selectedGroupId = ref('')

	/** The currently selected group item (null for default) */
	const selectedGroup = computed<GroupItem | null>(() => {
		if (!selectedGroupId.value) return null
		return sendentGroups.value.find(g => g.gid === selectedGroupId.value) ?? null
	})

	/** Load initial groups from PHP provideInitialState */
	function loadInitialState() {
		try {
			ncGroups.value = loadState('sendent', 'ncGroups') as GroupItem[]
		} catch {
			ncGroups.value = []
		}
		try {
			sendentGroups.value = loadState('sendent', 'sendentGroups') as GroupItem[]
		} catch {
			sendentGroups.value = []
		}
	}

	/**
	 * Move a group from NC groups to Sendent groups
	 * @param gid
	 */
	function addGroup(gid: string) {
		const index = ncGroups.value.findIndex(g => g.gid === gid)
		if (index === -1) return
		const [group] = ncGroups.value.splice(index, 1)
		sendentGroups.value.push(group)
		syncToBackend()
	}

	/**
	 * Move a group from Sendent groups back to NC groups
	 * @param gid
	 */
	function removeGroup(gid: string) {
		const index = sendentGroups.value.findIndex(g => g.gid === gid)
		if (index === -1) return
		const [group] = sendentGroups.value.splice(index, 1)
		// Only add back if not a deleted group
		if (!group.displayName.includes('*** DELETED GROUP ***')) {
			ncGroups.value.push(group)
			ncGroups.value.sort((a, b) => a.gid.localeCompare(b.gid))
		}
		syncToBackend()
	}

	/**
	 * Reorder Sendent groups (after drag-and-drop)
	 * @param newOrder
	 */
	function reorderGroups(newOrder: GroupItem[]) {
		sendentGroups.value = newOrder
		syncToBackend()
	}

	/**
	 * Select a group
	 * @param gid
	 */
	function selectGroup(gid: string) {
		selectedGroupId.value = gid
	}

	/** Sync current Sendent group list to backend */
	async function syncToBackend() {
		const gids = sendentGroups.value.map(g => g.gid)
		await api.updateGroups(gids)
	}

	return {
		ncGroups,
		sendentGroups,
		selectedGroupId,
		selectedGroup,
		loadInitialState,
		addGroup,
		removeGroup,
		reorderGroups,
		selectGroup,
	}
})
