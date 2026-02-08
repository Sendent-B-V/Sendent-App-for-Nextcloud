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
