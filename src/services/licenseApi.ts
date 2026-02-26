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
 * Registers a new license for a given Nextcloud group.
 *
 * @param email - Email address associated with the license
 * @param license - License key string
 * @param ncgroup - Nextcloud group ID to assign the license to
 * @return The resulting license status after creation
 */
export async function createLicense(email: string, license: string, ncgroup: string): Promise<LicenseStatus> {
	const url = generateUrl(`${baseUrl}/license`)
	const response = await axios.post(url, { email, license, ncgroup })
	return response.data
}

/**
 * Deletes the license associated with a Nextcloud group.
 *
 * @param group - Nextcloud group ID whose license should be removed
 */
export async function deleteLicense(group: string): Promise<void> {
	const url = generateUrl(`${baseUrl}/license`)
	await axios.delete(url, { data: { group } })
}

/**
 * Fetches the current license status for a given Nextcloud group.
 *
 * @param ncgroup - Nextcloud group ID to check the license for
 * @return Current license status for the group
 */
export async function fetchLicenseStatus(ncgroup: string): Promise<LicenseStatus> {
	const url = generateUrl(`${baseUrl}/licensestatusForNCGroupinternal`)
	const response = await axios.get(url, { params: { ncgroup } })
	return response.data
}

/**
 * Fetches the current app version and update status.
 *
 * @return App version information including available updates
 */
export async function fetchAppStatus(): Promise<AppVersionStatus> {
	const url = generateUrl(`${baseUrl}/status`)
	const response = await axios.get(url)
	return response.data
}

/**
 * Fetches a summary report of all active licenses.
 *
 * @return License report as a raw string
 */
export async function fetchLicenseReport(): Promise<string> {
	const url = generateUrl(`${baseUrl}/licensereport`)
	const response = await axios.get(url)
	return response.data
}
