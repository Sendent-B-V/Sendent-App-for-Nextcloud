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
 * Fetches a system tag by its ID.
 *
 * @param id - The numeric ID of the tag to retrieve
 * @return Tag data including visibility and assignability
 */
export async function fetchTag(id: number): Promise<TagData> {
	const url = generateUrl(`${baseUrl}/tag/{id}`, { id })
	const response = await axios.get(url)
	return response.data
}

/**
 * Creates a new system tag with the given name.
 *
 * @param name - Display name for the new tag
 * @return The newly created tag data
 */
export async function createTag(name: string): Promise<TagData> {
	const url = generateUrl(`${baseUrl}/tag`)
	const response = await axios.post(url, { name })
	return response.data
}

/**
 * Fetches all global workflow definitions from the Nextcloud workflow engine.
 *
 * @return A map of workflow class names to their workflow definitions
 */
export async function fetchWorkflows(): Promise<Record<string, WorkflowData[]>> {
	const url = generateUrl('/ocs/v2.php/apps/workflowengine/api/v1/workflows/global?format=json')
	const response = await axios.get(url, {
		headers: { 'OCS-APIRequest': 'true' },
	})
	return response.data.ocs.data
}

/**
 * Creates a new global workflow rule in the Nextcloud workflow engine.
 *
 * @param workflow - Workflow configuration to create
 * @param workflow.checks - Conditions that trigger the workflow
 * @param workflow.class - The workflow operation class identifier
 * @param workflow.entity - Entity type the workflow applies to (e.g. files)
 * @param workflow.events - Events that trigger evaluation of the workflow
 * @param workflow.name - Display name for the workflow rule
 * @param workflow.operation - Operation-specific configuration string
 * @return The newly created workflow definition
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
 * Fetches all file retention rules from the files_retention app.
 *
 * @return List of configured retention rules
 */
export async function fetchRetentions(): Promise<RetentionRule[]> {
	const url = generateUrl('/ocs/v2.php/apps/files_retention/api/v1/retentions?format=json')
	const response = await axios.get(url, {
		headers: { 'OCS-APIRequest': 'true' },
	})
	return response.data.ocs?.data ?? response.data
}

/**
 * Creates a new file retention rule linked to a system tag.
 *
 * @param retention - Retention rule configuration
 * @param retention.tagid - System tag ID that triggers this retention rule
 * @param retention.timeafter - Time reference point (e.g. creation or modification date)
 * @param retention.timeamount - Number of time units before files are removed
 * @param retention.timeunit - Unit of time (e.g. days, weeks, months)
 * @return The newly created retention rule
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
