import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

/**
 *
 * @param newSendentGroups
 */
export async function updateGroups(newSendentGroups: string[]): Promise<void> {
	const url = generateUrl('/apps/sendent/api/2.0/groups/update')
	await axios.post(url, { newSendentGroups })
}
