import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const baseUrl = '/apps/sendent/api/1.0'

export interface TermsAgreement {
	Version: string
	Agreed: string
}

/**
 *
 * @param version
 */
export async function checkAgreement(version: string): Promise<TermsAgreement> {
	const url = generateUrl(`${baseUrl}/termsagreement/isagreed/{version}`, { version })
	const response = await axios.get(url)
	return response.data
}

/**
 *
 * @param version
 */
export async function markAgreed(version: string): Promise<TermsAgreement> {
	const url = generateUrl(`${baseUrl}/termsagreement/agree/{version}`, { version })
	const response = await axios.get(url)
	return response.data
}
