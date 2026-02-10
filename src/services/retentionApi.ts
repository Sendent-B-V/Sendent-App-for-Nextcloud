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
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export interface TagData {
	id: number
	name: string
	userVisible: boolean
	userAssignable: boolean
}

export interface WorkflowCheck {
	class: string
	operator: string
	value: string
}

export interface WorkflowData {
	id: string
	class: string
	name: string
	checks: WorkflowCheck[]
	operation: string
	entity: string
	events: unknown[]
	scope_type: string
	scope_actor_id: string
}

export interface RetentionRule {
	id: number
	tagid: number
	timeafter: number
	timeamount: number
	timeunit: number
}

const baseUrl = '/apps/sendent/api/1.0'

/**
 *
 * @param id
 */
export async function fetchTag(id: number): Promise<TagData> {
	const url = generateUrl(`${baseUrl}/tag/{id}`, { id })
	const response = await axios.get(url)
	return response.data
}

/**
 *
 * @param name
 */
export async function createTag(name: string): Promise<TagData> {
	const url = generateUrl(`${baseUrl}/tag`)
	const response = await axios.post(url, { name })
	return response.data
}

/**
 *
 */
export async function fetchWorkflows(): Promise<Record<string, WorkflowData[]>> {
	const url = generateUrl('/ocs/v2.php/apps/workflowengine/api/v1/workflows/global?format=json')
	const response = await axios.get(url, {
		headers: { 'OCS-APIRequest': 'true' },
	})
	return response.data.ocs.data
}

/**
 *
 * @param workflow
 * @param workflow.checks
 * @param workflow.class
 * @param workflow.entity
 * @param workflow.events
 * @param workflow.name
 * @param workflow.operation
 */
export async function createWorkflow(workflow: {
	checks: WorkflowCheck[]
	class: string
	entity: string
	events: string[]
	name: string
	operation: string
}): Promise<WorkflowData> {
	const url = generateUrl('/ocs/v2.php/apps/workflowengine/api/v1/workflows/global?format=json')
	const response = await axios.post(url, workflow, {
		headers: { 'OCS-APIRequest': 'true' },
	})
	return response.data.ocs.data
}

/**
 *
 */
export async function fetchRetentions(): Promise<RetentionRule[]> {
	const url = generateUrl('/ocs/v2.php/apps/files_retention/api/v1/retentions?format=json')
	const response = await axios.get(url, {
		headers: { 'OCS-APIRequest': 'true' },
	})
	return response.data.ocs?.data ?? response.data
}

/**
 *
 * @param retention
 * @param retention.tagid
 * @param retention.timeafter
 * @param retention.timeamount
 * @param retention.timeunit
 */
export async function createRetention(retention: {
	tagid: number
	timeafter: number
	timeamount: number
	timeunit: number
}): Promise<RetentionRule> {
	const url = generateUrl('/ocs/v2.php/apps/files_retention/api/v1/retentions?format=json')
	const response = await axios.post(url, retention, {
		headers: { 'OCS-APIRequest': 'true' },
	})
	return response.data.ocs?.data ?? response.data
}
