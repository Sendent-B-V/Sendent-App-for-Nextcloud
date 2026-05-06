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
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { generateUrl } from '@nextcloud/router'
import { fetchCapabilities } from '../services/capabilitiesApi'

export interface AppDependency {
	id: string
	name: string
	installed: boolean
	required: boolean
}

const REQUIRED_APPS = [
	{ id: 'core', name: 'Core' },
	{ id: 'files', name: 'Files' },
	{ id: 'dav', name: 'WebDAV' },
	{ id: 'ocm', name: 'OCM' },
	{ id: 'files_sharing', name: 'Files Sharing' },
	{ id: 'password_policy', name: 'Password Policy' },
	{ id: 'theming', name: 'Theming' },
]

const RECOMMENDED_APPS = [
	{ id: 'activity', name: 'Activity' },
	{ id: 'spreed', name: 'Talk' },
]

export const useDependenciesStore = defineStore('dependencies', () => {
	const requiredApps = ref<AppDependency[]>([])
	const recommendedApps = ref<AppDependency[]>([])
	const loading = ref(false)
	// Initial fallback URL used until capabilities resolve. Two requirements:
	//  - Use key='logo' (not 'logoheader'): the 'logo' endpoint always returns
	//    200 (falls back to the default logo when no custom one is uploaded),
	//    while 'logoheader' returns 404 + Cache-Control:max-age=3600 if no
	//    custom header was set, poisoning the browser cache for an hour.
	//  - Append a cache buster: even with a valid URL, a stale 404 from a
	//    prior session must not be served from cache. Date.now() is replaced
	//    by the proper ?v=<cacheBuster> from capabilities once they resolve.
	const themingLogoUrl = ref(`${generateUrl('/apps/theming/image/logo')}?v=${Date.now()}`)
	const shareExpirationDays = ref(0)
	const shareExpirationEnforced = ref(false)

	/** Check which required/recommended apps are installed */
	async function checkDependencies() {
		loading.value = true
		try {
			const capabilities = await fetchCapabilities()
			const capKeys = Object.keys(capabilities)

			requiredApps.value = REQUIRED_APPS.map(app => ({
				...app,
				installed: capKeys.includes(app.id),
				required: true,
			}))

			recommendedApps.value = RECOMMENDED_APPS.map(app => ({
				...app,
				installed: capKeys.includes(app.id),
				required: false,
			}))

			// Extract versioned logo URL from theming capabilities
			const theming = capabilities.theming as Record<string, unknown> | undefined
			if (theming && typeof theming.logoheader === 'string') {
				themingLogoUrl.value = theming.logoheader
			} else if (theming && typeof theming.logo === 'string') {
				themingLogoUrl.value = theming.logo
			}
			// Extract share expiration limits from files_sharing capabilities
			const filesSharing = capabilities.files_sharing as Record<string, unknown> | undefined
			if (filesSharing) {
				const publicCaps = filesSharing.public as Record<string, unknown> | undefined
				if (publicCaps?.expire_date) {
					const expDate = publicCaps.expire_date as Record<string, unknown>
					shareExpirationDays.value = Number(expDate.days) || 0
					shareExpirationEnforced.value = Boolean(expDate.enforced)
				}
			}
		} catch {
			requiredApps.value = REQUIRED_APPS.map(app => ({
				...app,
				installed: false,
				required: true,
			}))
			recommendedApps.value = RECOMMENDED_APPS.map(app => ({
				...app,
				installed: false,
				required: false,
			}))
		} finally {
			loading.value = false
		}
	}

	return {
		requiredApps,
		recommendedApps,
		loading,
		themingLogoUrl,
		shareExpirationDays,
		shareExpirationEnforced,
		checkDependencies,
	}
})
