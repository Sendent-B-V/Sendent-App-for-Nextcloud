<template>
	<div class="license-section">
		<h3>License</h3>
		<div v-if="licenseStore.loading" class="license-section__loading">
			<span class="icon-loading" />
			Loading license status...
		</div>
		<template v-else>
			<!-- Inheritance checkbox for non-default groups -->
			<div v-if="groupsStore.selectedGroupId" class="license-section__inherit">
				<label>
					<input type="checkbox"
						:checked="licenseStore.isInherited()"
						@change="onToggleInherit">
					Use default group license
				</label>
			</div>

			<!-- License form -->
			<div class="license-section__form">
				<div class="license-section__field">
					<label>Email address</label>
					<input v-model="licenseStore.email"
						type="email"
						:disabled="isDisabled"
						placeholder="Enter email address">
				</div>
				<div class="license-section__field">
					<label>License key</label>
					<input v-model="licenseStore.licenseKey"
						type="text"
						:disabled="isDisabled"
						placeholder="Enter license key">
				</div>
				<div class="license-section__actions">
					<button class="primary"
						:disabled="isDisabled || !licenseStore.email || !licenseStore.licenseKey"
						@click="onActivate">
						Activate License
					</button>
					<button :disabled="isDisabled"
						@click="onClear">
						Clear License
					</button>
				</div>
			</div>

			<!-- License Status -->
			<LicenseStatusDisplay :status="licenseStore.status" />

			<!-- Outlook Add-in Version (hidden for now) -->
		</template>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useLicenseStore } from '../../stores/license'
import { useGroupsStore } from '../../stores/groups'
import LicenseStatusDisplay from './LicenseStatusDisplay.vue'

const licenseStore = useLicenseStore()
const groupsStore = useGroupsStore()

const isDisabled = computed(() =>
	groupsStore.selectedGroupId !== '' && licenseStore.isInherited(),
)

/**
 *
 */
async function onActivate() {
	await licenseStore.activateLicense(groupsStore.selectedGroupId)
}

/**
 *
 */
async function onClear() {
	await licenseStore.clearLicense(groupsStore.selectedGroupId)
}

/**
 *
 * @param event
 */
async function onToggleInherit(event: Event) {
	const target = event.target as HTMLInputElement
	if (target.checked) {
		// Clear group-specific license to inherit default
		await licenseStore.clearLicense(groupsStore.selectedGroupId)
	}
}
</script>

<style scoped>
.license-section {
	margin-bottom: 24px;
}

.license-section h3 {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 16px;
}

.license-section__loading {
	display: flex;
	align-items: center;
	gap: 8px;
	color: var(--color-text-maxcontrast);
}

.license-section__inherit {
	margin-bottom: 12px;
}

.license-section__inherit label {
	display: flex;
	align-items: center;
	gap: 6px;
	cursor: pointer;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
}

.license-section__form {
	margin-bottom: 16px;
}

.license-section__field {
	margin-bottom: 8px;
}

.license-section__field label {
	display: block;
	font-weight: 500;
	margin-bottom: 4px;
}

.license-section__field input {
	width: 100%;
	max-width: 400px;
}

.license-section__actions {
	display: flex;
	gap: 8px;
	margin-top: 12px;
}

</style>
