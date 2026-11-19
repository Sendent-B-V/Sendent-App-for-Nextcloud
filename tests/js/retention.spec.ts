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

/**
 * Regression tests for the retention assistant on NC 33+:
 * - the tag id was saved via OCP.AppConfig.setValue(), which since NC 33
 *   posts JSON and gets a silent 400 for numeric values → config stayed empty
 * - re-clicking "Create rule" then hit files_retention's duplicate-rule 400
 */
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'

const initialState = vi.hoisted(() => ({ values: {} as Record<string, unknown> }))

vi.mock('@nextcloud/initial-state', () => ({
	loadState: (app: string, key: string) => {
		if (!(key in initialState.values)) {
			throw new Error(`Could not find initial state ${key} of ${app}`)
		}
		return initialState.values[key]
	},
}))

vi.mock('../../src/services/retentionApi', () => ({
	createTag: vi.fn(),
	fetchRetentions: vi.fn(),
	createRetention: vi.fn(),
	fetchWorkflows: vi.fn(),
	createWorkflow: vi.fn(),
}))

/* eslint-disable import/first */
import * as retentionApi from '../../src/services/retentionApi'
import { useRetentionStore } from '../../src/stores/retention'
/* eslint-enable import/first */

const api = vi.mocked(retentionApi)
const unconfigured = () => ({ 'tag:upload': -1, 'tag:removed': -1, 'tag:expired': -1 })

describe('retention store', () => {
	let legacySetValue: ReturnType<typeof vi.fn>

	beforeEach(() => {
		setActivePinia(createPinia())
		vi.clearAllMocks()
		initialState.values = {
			tags: unconfigured(),
			apps: { files_retention: '1.0.0', files_automatedtagging: '1.0.0' },
		}
		// Trap: the old code saved the id through this browser API
		legacySetValue = vi.fn()
		;(globalThis as Record<string, unknown>).OCP = { AppConfig: { setValue: legacySetValue } }
	})

	describe('ensureTag', () => {
		it('lets the server persist the id instead of using OCP.AppConfig', async () => {
			api.createTag.mockResolvedValue({ id: 8, name: 'retention:removed', userVisible: true, userAssignable: false })
			const store = useRetentionStore()
			store.loadInitialState()

			const id = await store.ensureTag('tag:removed')

			expect(id).toBe(8)
			expect(api.createTag).toHaveBeenCalledWith('retention:removed', 'tag:removed')
			expect(legacySetValue).not.toHaveBeenCalled()
		})

		it('reuses an already configured tag', async () => {
			initialState.values.tags = { 'tag:upload': 1, 'tag:removed': 2, 'tag:expired': 3 }
			const store = useRetentionStore()
			store.loadInitialState()

			expect(await store.ensureTag('tag:expired')).toBe(3)
			expect(api.createTag).not.toHaveBeenCalled()
		})

		it('does not mutate the cached initial state object', async () => {
			const tags = unconfigured()
			initialState.values.tags = tags
			api.createTag.mockResolvedValue({ id: 9, name: 'retention:expired', userVisible: true, userAssignable: false })
			const store = useRetentionStore()
			store.loadInitialState()

			await store.ensureTag('tag:expired')

			expect(tags['tag:expired']).toBe(-1)
			expect(store.tags['tag:expired']).toBe(9)
		})
	})

	describe('createRetentionRule', () => {
		it('creates a rule when none exists for the tag', async () => {
			api.createTag.mockResolvedValue({ id: 8, name: 'retention:removed', userVisible: true, userAssignable: false })
			api.fetchRetentions.mockResolvedValue([])
			const store = useRetentionStore()
			store.loadInitialState()

			await store.createRetentionRule('tag:removed', 30, 0, 0)

			expect(api.createRetention).toHaveBeenCalledWith({ tagid: 8, timeafter: 0, timeamount: 30, timeunit: 0 })
			expect(store.hasRemovedRetention).toBe(true)
		})

		it('reuses an existing rule instead of posting a duplicate (files_retention answers 400 "tagid")', async () => {
			// State seen on affected installs: rule exists, tag id never saved
			api.createTag.mockResolvedValue({ id: 8, name: 'retention:removed', userVisible: true, userAssignable: false })
			api.fetchRetentions.mockResolvedValue([{ id: 6, tagid: 8, timeafter: 0, timeamount: 30, timeunit: 0 }])
			const store = useRetentionStore()
			store.loadInitialState()

			await store.createRetentionRule('tag:removed', 30, 0, 0)

			expect(api.createRetention).not.toHaveBeenCalled()
			expect(store.hasRemovedRetention).toBe(true)
		})

		it('propagates a failing rule creation instead of marking the step done', async () => {
			initialState.values.tags = { 'tag:upload': 1, 'tag:removed': 2, 'tag:expired': 3 }
			api.fetchRetentions.mockResolvedValue([])
			api.createRetention.mockRejectedValue(new Error('Request failed with status code 400'))
			const store = useRetentionStore()
			store.loadInitialState()

			await expect(store.createRetentionRule('tag:expired', 90, 0, 0)).rejects.toThrow('400')
			expect(store.hasExpiredRetention).toBe(false)
		})
	})

	describe('checkConfiguration', () => {
		it('detects existing rules for the configured tags', async () => {
			initialState.values.tags = { 'tag:upload': 10, 'tag:removed': 8, 'tag:expired': 9 }
			api.fetchWorkflows.mockResolvedValue({})
			api.fetchRetentions.mockResolvedValue([
				{ id: 6, tagid: 8, timeafter: 0, timeamount: 30, timeunit: 0 },
				{ id: 7, tagid: 9, timeafter: 0, timeamount: 90, timeunit: 0 },
			])
			const store = useRetentionStore()
			store.loadInitialState()

			await store.checkConfiguration()

			expect(store.hasRemovedRetention).toBe(true)
			expect(store.hasExpiredRetention).toBe(true)
		})
	})
})
