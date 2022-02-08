import { generateOcsUrl, generateUrl } from '@nextcloud/router';
import axios from '@nextcloud/axios';

export interface TagData {
    id: number
    name: string
    isVisible: boolean
    isAssignable: boolean
}

export interface WorkflowData {
    id: string
    class: string
    name: string
    checks: { class: string, operator: 'is' | 'matches', value: string }[]
    operation: string
    entity: string
    events: any[]
    scope_type: string
    scope_actor_id: string
}

export interface RetentionData {
    id: number,
    tagid: number,
    timeunit: number,
    timeamount: number,
    timeafter: number,
}

export enum RetentionUnit { Days, Weeks, Months, Years }
export enum RetentionAfter { Creation, Modification }

class API {
    public async getWorkflows(): Promise<{ [operation: string]: WorkflowData[] }> {
        const url = generateOcsUrl('apps/workflowengine/api/v1/workflows/global?format=json');
        const response = await axios.get(url);
        const data = response.data?.ocs || {};

        if (data.meta?.statuscode !== 200) {
            throw new Error('Could not get workflows: ' + data.meta?.message);
        }

        return data.data;
    }

    public async createWorkflow(tagId: number) {
        const url = generateOcsUrl('apps/workflowengine/api/v1/workflows/global?format=json');
        const data = {
            checks: [{
                class: 'OCA\\WorkflowEngine\\Check\\RequestUserAgent',
                operator: 'is',
                value: 'mail',
                invalid: false
            }],
            class: 'OCA\\FilesAutomatedTagging\\Operation',
            entity: 'OCA\\WorkflowEngine\\Entity\\File',
            events: [],
            name: '',
            operation: tagId.toString(),
        };
        const response = await axios.post(url, data);
        const ocs = response.data?.ocs || {};

        if (ocs.meta?.statuscode !== 200) {
            throw new Error('Could not create workflow: ' + ocs.meta?.message);
        }

        return ocs.data;
    }

    public async getRetentions() {
        const url = generateUrl('apps/files_retention/api/v1/retentions');
        const response = await axios.get<RetentionData[]>(url);

        return response.data;
    }

    public async createRetention(tagId: number, timeAmount: number, timeUnit = RetentionUnit.Days, timeAfter = RetentionAfter.Modification) {
        const url = generateUrl('apps/files_retention/api/v1/retentions');
        const response = await axios.post<RetentionData>(url, {
            tagid: tagId,
            timeafter: timeAfter,
            timeamount: timeAmount,
            timeunit: timeUnit,
        });

        return response.data;
    }

    public async getTag(tagId: number) {
        const url = generateUrl('apps/sendent/api/1.0/tag/{id}', { id: tagId });
        const response = await axios.get<TagData>(url);

        return response.data;
    }

    public async createTag(name: string) {
        const url = generateUrl('apps/sendent/api/1.0/tag');
        const response = await axios.post<TagData>(url, { name });

        return response.data;
    }
}

export const api = new API();
