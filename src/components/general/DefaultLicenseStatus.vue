<template>
	<div class="default-license">
		<h3>Default License Status</h3>
		<div class="default-license__grid">
			<div class="default-license__row">
				<span class="default-license__label">Status:</span>
				<span class="default-license__status" v-html="licenseStore.defaultLicense.status || 'Not configured'" />
			</div>
			<div v-if="licenseStore.defaultLicense.level" class="default-license__row">
				<span class="default-license__label">Level:</span>
				<span>{{ licenseStore.defaultLicense.level }}</span>
			</div>
			<div v-if="licenseStore.defaultLicense.expirationDate" class="default-license__row">
				<span class="default-license__label">Expiration:</span>
				<span>{{ licenseStore.defaultLicense.expirationDate }}</span>
			</div>
			<div v-if="licenseStore.defaultLicense.lastCheck" class="default-license__row">
				<span class="default-license__label">Last check:</span>
				<span>{{ licenseStore.defaultLicense.lastCheck }}</span>
			</div>
		</div>
		<button class="default-license__report" @click="licenseStore.downloadReport()">
			Download License Report
		</button>
	</div>
</template>

<script setup lang="ts">
import { useLicenseStore } from '../../stores/license'

const licenseStore = useLicenseStore()
</script>

<style scoped>
.default-license {
	margin-bottom: 24px;
}

.default-license h3 {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 12px;
}

.default-license__grid {
	margin-bottom: 12px;
}

.default-license__row {
	display: flex;
	gap: 8px;
	padding: 4px 0;
}

.default-license__label {
	font-weight: 500;
	min-width: 100px;
	color: var(--color-text-maxcontrast);
}

.default-license__status {
	font-weight: 600;
}

.default-license__report {
	margin-top: 8px;
}
</style>
