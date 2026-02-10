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
import * as api from '../services/termsApi'

const TERMS_VERSION = '1.2.8'

export const useTermsStore = defineStore('terms', () => {
	const agreed = ref(false)
	const loading = ref(false)

	/** Check if the user has agreed to the terms */
	async function checkAgreement() {
		loading.value = true
		try {
			const result = await api.checkAgreement(TERMS_VERSION)
			agreed.value = result.Agreed === 'yes'
		} catch {
			agreed.value = false
		} finally {
			loading.value = false
		}
	}

	/** Mark terms as agreed */
	async function agree() {
		loading.value = true
		try {
			const result = await api.markAgreed(TERMS_VERSION)
			agreed.value = result.Agreed === 'yes'
		} finally {
			loading.value = false
		}
	}

	return {
		agreed,
		loading,
		checkAgreement,
		agree,
	}
})
