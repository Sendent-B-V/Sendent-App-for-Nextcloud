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
