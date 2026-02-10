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
