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
import { ref } from 'vue'
import { loadState } from '@nextcloud/initial-state'
import * as retentionApi from '../services/retentionApi'

interface TagState {
	'tag:upload': number
	'tag:removed': number
	'tag:expired': number
}

const DEFAULT_TAG_NAMES: Record<string, string> = {
	'tag:upload': 'outlook:upload',
	'tag:removed': 'retention:removed',
	'tag:expired': 'retention:expired',
}

export const useRetentionStore = defineStore('retention', () => {
	/** Tag IDs for retention (-1 = not configured) */
	const tags = ref<TagState>({ 'tag:upload': -1, 'tag:removed': -1, 'tag:expired': -1 })
	/** Whether files_automatedtagging is installed */
	const hasTaggingApp = ref(false)
	/** Whether files_retention is installed */
	const hasRetentionApp = ref(false)
	/** App versions from initialState */
	const appVersions = ref<Record<string, string | false>>({})
	/** Whether a workflow exists for the upload tag */
	const hasUploadWorkflow = ref(false)
	/** Whether retention rules exist for removed/expired tags */
	const hasRemovedRetention = ref(false)
	const hasExpiredRetention = ref(false)
	/** Loading state */
	const loading = ref(false)

	/** Load initial state from PHP */
	function loadInitialState() {
		try {
			const state = loadState('sendent', 'tags') as TagState
			tags.value = state
		} catch {
			// Tags not configured
		}
		try {
			const apps = loadState('sendent', 'apps') as Record<string, string | false>
			appVersions.value = apps
			hasTaggingApp.value = apps.files_automatedtagging !== false
			hasRetentionApp.value = apps.files_retention !== false
		} catch {
			// Apps not available
		}
	}

	/**
	 * Ensure a tag exists, creating it if needed
	 * @param key
	 */
	async function ensureTag(key: keyof TagState): Promise<number> {
		if (tags.value[key] >= 0) {
			return tags.value[key]
		}
		const tag = await retentionApi.createTag(DEFAULT_TAG_NAMES[key])
		tags.value[key] = tag.id

		// Save to NC app config
		OCP.AppConfig.setValue('sendent', key, tag.id)
		return tag.id
	}

	/** Check if workflows and retention rules are configured */
	async function checkConfiguration() {
		loading.value = true
		try {
			// Check workflows
			if (hasTaggingApp.value && tags.value['tag:upload'] >= 0) {
				try {
					const workflows = await retentionApi.fetchWorkflows()
					const tagAssignClass = 'OCA\\FilesAutomatedTagging\\Operation'
					const tagWorkflows = workflows[tagAssignClass] ?? []
					hasUploadWorkflow.value = tagWorkflows.some(
						(w: retentionApi.WorkflowData) => w.operation === String(tags.value['tag:upload']),
					)
				} catch {
					// Workflow engine API not available
				}
			}

			// Check retention rules
			if (hasRetentionApp.value) {
				try {
					const retentions = await retentionApi.fetchRetentions()
					hasRemovedRetention.value = retentions.some(
						(r: retentionApi.RetentionRule) => r.tagid === tags.value['tag:removed'],
					)
					hasExpiredRetention.value = retentions.some(
						(r: retentionApi.RetentionRule) => r.tagid === tags.value['tag:expired'],
					)
				} catch {
					// Retention API not available
				}
			}
		} finally {
			loading.value = false
		}
	}

	/** Create the upload tag workflow */
	async function createUploadWorkflow() {
		const tagId = await ensureTag('tag:upload')
		await retentionApi.createWorkflow({
			checks: [{
				class: 'OCA\\WorkflowEngine\\Check\\RequestUserAgent',
				operator: 'matches',
				value: '/.*Sendent.*/',
			}],
			class: 'OCA\\FilesAutomatedTagging\\Operation',
			entity: 'OCA\\WorkflowEngine\\Entity\\File',
			events: [],
			name: 'Sendent upload tag',
			operation: String(tagId),
		})
		hasUploadWorkflow.value = true
	}

	/**
	 * Create a retention rule for a tag
	 * @param tagKey
	 * @param timeamount
	 * @param timeunit
	 * @param timeafter
	 */
	async function createRetentionRule(
		tagKey: keyof TagState,
		timeamount: number,
		timeunit: number,
		timeafter: number,
	) {
		const tagId = await ensureTag(tagKey)
		await retentionApi.createRetention({
			tagid: tagId,
			timeafter,
			timeamount,
			timeunit,
		})
		if (tagKey === 'tag:removed') hasRemovedRetention.value = true
		if (tagKey === 'tag:expired') hasExpiredRetention.value = true
	}

	return {
		tags,
		hasTaggingApp,
		hasRetentionApp,
		appVersions,
		hasUploadWorkflow,
		hasRemovedRetention,
		hasExpiredRetention,
		loading,
		loadInitialState,
		ensureTag,
		checkConfiguration,
		createUploadWorkflow,
		createRetentionRule,
	}
})
