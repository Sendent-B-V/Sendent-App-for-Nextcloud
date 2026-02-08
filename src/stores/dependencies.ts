import { defineStore } from 'pinia'
import { ref } from 'vue'
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
		checkDependencies,
	}
})
