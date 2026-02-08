import axios from '@nextcloud/axios'

interface OCSCapabilities {
	ocs: {
		data: {
			capabilities: Record<string, unknown>
		}
	}
}

/**
 *
 */
export async function fetchCapabilities(): Promise<Record<string, unknown>> {
	const response = await axios.get<OCSCapabilities>('/ocs/v1.php/cloud/capabilities', {
		headers: { 'OCS-APIRequest': 'true' },
	})
	return response.data.ocs.data.capabilities
}
