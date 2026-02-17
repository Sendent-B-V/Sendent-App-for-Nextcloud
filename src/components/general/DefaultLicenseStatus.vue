<!--
  - @copyright Copyright (c) 2026 Sendent B.V.
  -
  - @author Sendent B.V. <info@sendent.com>
  -
  - @license AGPL-3.0-or-later
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -->
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
		<button class="button primary" @click="licenseStore.downloadReport()">
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

</style>
