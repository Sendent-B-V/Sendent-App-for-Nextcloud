import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import type { SettingValue } from '../types/settings'

const baseUrl = '/apps/sendent/api/1.0'

/**
 *
 */
export async function fetchSettingsForDefaultGroup(): Promise<SettingValue[]> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue/getForDefaultGroup`)
	const response = await axios.get(url)
	return response.data
}

/**
 *
 * @param gid
 */
export async function fetchSettingsForGroup(gid: string): Promise<SettingValue[]> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue/getForNCGroup/{gid}`, { gid })
	const response = await axios.get(url)
	return response.data
}

/**
 *
 * @param settingkeyid
 */
export async function fetchSettingByKeyId(settingkeyid: number): Promise<SettingValue> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue/showbysettingkeyid/{id}`, { id: settingkeyid })
	const response = await axios.get(url)
	return response.data
}

/**
 *
 * @param settingkeyid
 * @param value
 * @param groupid
 * @param group
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
 *
 * @param settingkeyid
 * @param value
 * @param groupid
 * @param group
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
 *
 * @param settingkeyid
 * @param ncgroup
 */
export async function deleteSettingValue(settingkeyid: number, ncgroup: string): Promise<SettingValue> {
	const url = generateUrl(`${baseUrl}/settinggroupvalue/{id}`, { id: settingkeyid })
	const response = await axios.delete(url, { data: { ncgroup } })
	return response.data
}

/**
 *
 * @param name
 * @param key
 * @param valuetype
 * @param templateid
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
