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
import { loadState } from '@nextcloud/initial-state'
import type { LicenseStatus, DefaultLicenseState, VSTOAddinVersion } from '../types/license'
import * as api from '../services/licenseApi'

export const useLicenseStore = defineStore('license', () => {
	/** License status for currently selected group */
	const status = ref<LicenseStatus | null>(null)
	/** Default license state from PHP initialState */
	const defaultLicense = ref<DefaultLicenseState>({
		status: '',
		level: '',
		expirationDate: '',
		lastCheck: '',
	})
	/** Latest Outlook add-in version info */
	const addinVersion = ref<VSTOAddinVersion | null>(null)
	/** Loading state */
	const loading = ref(false)
	/** Email input for license form */
	const email = ref('')
	/** Key input for license form */
	const licenseKey = ref('')

	/** Load default license info from PHP initialState */
	function loadInitialState() {
		try {
			const state = loadState('sendent', 'defaultLicense') as DefaultLicenseState
			defaultLicense.value = state
		} catch {
			// No default license configured
		}
	}

	/**
	 * Fetches the current license status and app version info for a group.
	 * Pre-fills the email and license key fields from the server response.
	 *
	 * @param ncgroup - Nextcloud group ID to fetch the status for
	 */
	async function refreshStatus(ncgroup: string) {
		loading.value = true
		try {
			const [licenseStatus, appStatus] = await Promise.all([
				api.fetchLicenseStatus(ncgroup),
				api.fetchAppStatus(),
			])
			status.value = licenseStatus
			addinVersion.value = appStatus.LatestVSTOAddinVersion ?? null

			// Pre-fill email and key from fetched status
			email.value = licenseStatus.email || ''
			licenseKey.value = licenseStatus.licensekey || ''
		} catch {
			status.value = null
		} finally {
			loading.value = false
		}
	}

	/**
	 * Activate a license for a group and refresh the status regardless of outcome.
	 *
	 * @param ncgroup - Nextcloud group ID to activate the license for
	 */
	async function activateLicense(ncgroup: string) {
		loading.value = true
		try {
			await api.createLicense(
				email.value.trim(),
				licenseKey.value.trim(),
				ncgroup,
			)
		} catch (error) {
			console.error('License deletion failed:', error)
		} finally {
			await refreshStatus(ncgroup)
			loading.value = false
		}
	}

	/**
	 * Clear/delete the license for a group, reverting to the inherited default.
	 *
	 * @param ncgroup - Nextcloud group ID whose license should be cleared
	 */
	async function clearLicense(ncgroup: string) {
		loading.value = true
		try {
			await api.deleteLicense(ncgroup)
			email.value = ''
			licenseKey.value = ''
		} catch (error) {
			console.error('License deletion failed:', error)
		} finally {
			await refreshStatus(ncgroup)
			loading.value = false
		}
	}

	/** Download license report as CSV */
	async function downloadReport() {
		const data = await api.fetchLicenseReport()
		const licenses = JSON.parse(data)
		let csv = 'Group,Key,Email,Expiration date,Level,Inherited\n'
		for (const row of licenses) {
			csv += `${row.ncgroup},${row.licensekey},${row.email},${row.datelicenseend},${row.level},${row.inherited}\n`
		}
		const link = document.createElement('a')
		link.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv)
		link.target = '_blank'
		link.download = 'SendentLicensesReport.csv'
		link.click()
	}

	/** Check if current group inherits the default license */
	function isInherited(): boolean {
		if (!status.value) return true
		return !status.value.ncgroup
	}

	return {
		status,
		defaultLicense,
		addinVersion,
		loading,
		email,
		licenseKey,
		loadInitialState,
		refreshStatus,
		activateLicense,
		clearLicense,
		downloadReport,
		isInherited,
	}
})
