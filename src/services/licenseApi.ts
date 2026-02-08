import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import type { LicenseStatus, AppVersionStatus } from '../types/license'

const baseUrl = '/apps/sendent/api/1.0'

/**
 *
 * @param email
 * @param license
 * @param ncgroup
 */
export async function createLicense(email: string, license: string, ncgroup: string): Promise<LicenseStatus> {
	const url = generateUrl(`${baseUrl}/license`)
	const response = await axios.post(url, { email, license, ncgroup })
	return response.data
}

/**
 *
 * @param group
 */
export async function deleteLicense(group: string): Promise<void> {
	const url = generateUrl(`${baseUrl}/license`)
	await axios.delete(url, { data: { group } })
}

/**
 *
 * @param ncgroup
 */
export async function fetchLicenseStatus(ncgroup: string): Promise<LicenseStatus> {
	const url = generateUrl(`${baseUrl}/licensestatusForNCGroupinternal`)
	const response = await axios.get(url, { params: { ncgroup } })
	return response.data
}

/**
 *
 */
export async function fetchAppStatus(): Promise<AppVersionStatus> {
	const url = generateUrl(`${baseUrl}/status`)
	const response = await axios.get(url)
	return response.data
}

/**
 *
 */
export async function fetchLicenseReport(): Promise<string> {
	const url = generateUrl(`${baseUrl}/licensereport`)
	const response = await axios.get(url)
	return response.data
}
