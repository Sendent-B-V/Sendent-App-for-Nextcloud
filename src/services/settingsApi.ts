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
import type { SettingValue } from '../types/settings'

const baseUrl = '/apps/sendent/api/1.0'

/**
 * Fetches all setting values for the default (global) group.
 *
 * @return List of setting values applied to the default group
 */
export async function fetchSettingsForDefaultGroup(): Promise<SettingValue[]> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue/getForDefaultGroup`)
	const response = await axios.get(url)
	return response.data
}

/**
 * Fetches all setting values for a specific Nextcloud group.
 *
 * @param gid - Nextcloud group ID to fetch settings for
 * @return List of setting values applied to the group
 */
export async function fetchSettingsForGroup(gid: string): Promise<SettingValue[]> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue/getForNCGroup/{gid}`, { gid })
	const response = await axios.get(url)
	return response.data
}

/**
 * Fetches a single setting value by its setting key ID.
 *
 * @param settingkeyid - Numeric ID of the setting key to look up
 * @return The matching setting value
 */
export async function fetchSettingByKeyId(settingkeyid: number): Promise<SettingValue> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue/showbysettingkeyid/{id}`, { id: settingkeyid })
	const response = await axios.get(url)
	return response.data
}

/**
 * Creates a new setting value entry for a given group.
 *
 * @param settingkeyid - Numeric ID of the setting key
 * @param value - The value to store
 * @param groupid - Group ID to associate the setting with
 * @param group - Optional group name for display purposes
 * @return The newly created setting value
 */
export async function createSettingValue(
	settingkeyid: number,
	value: string,
	groupid: string,
	group?: string,
): Promise<SettingValue> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue`)
	const response = await axios.post(url, { settingkeyid, value, groupid, group })
	return response.data
}

/**
 * Updates an existing setting value for a given group.
 *
 * @param settingkeyid - Numeric ID of the setting key to update
 * @param value - The new value to store
 * @param groupid - Group ID the setting belongs to
 * @param group - Optional group name for display purposes
 * @return The updated setting value
 */
export async function updateSettingValue(
	settingkeyid: number,
	value: string,
	groupid: string,
	group?: string,
): Promise<SettingValue> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue/{id}`, { id: settingkeyid })
	const response = await axios.put(url, { settingkeyid, value, groupid, group })
	return response.data
}

/**
 * Deletes a setting value, reverting it to the inherited/default value for the group.
 *
 * @param settingkeyid - Numeric ID of the setting key to delete
 * @param ncgroup - Nextcloud group ID the setting belongs to
 * @return The deleted setting value
 */
export async function deleteSettingValue(settingkeyid: number, ncgroup: string): Promise<SettingValue> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue/{id}`, { id: settingkeyid })
	const response = await axios.delete(url, { data: { ncgroup } })
	return response.data
}

/**
 * Creates a new setting key definition in the system.
 *
 * @param name - Display name for the setting
 * @param key - Unique key identifier string
 * @param valuetype - Data type of the setting value (e.g. 'string', 'number')
 * @param templateid - ID of the template this setting belongs to
 * @return Object containing the newly created setting key ID
 */
export async function createSettingKey(
	name: string,
	key: string,
	valuetype: string,
	templateid: number,
): Promise<{ key: number }> {
	const url = generateUrl(`${baseUrl}/settingkey`)
	const response = await axios.post(url, { name, key, valuetype, templateid })
	return response.data
}
