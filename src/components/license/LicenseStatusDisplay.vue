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
	<div v-if="status" class="license-status">
		<div class="license-status__row">
			<span class="license-status__label">Status:</span>
			<span class="license-status__value"
				:class="statusClass"
				v-html="status.status" />
		</div>
		<div v-if="status.level" class="license-status__row">
			<span class="license-status__label">Level:</span>
			<span class="license-status__value">
				{{ status.level === 'Offline_mode' ? 'Offline mode' : status.level }}
			</span>
		</div>
		<div v-if="status.dateExpiration" class="license-status__row">
			<span class="license-status__label">Expiration:</span>
			<span class="license-status__value">{{ formatDate(status.dateExpiration) }}</span>
		</div>
		<div v-if="status.dateLastCheck" class="license-status__row">
			<span class="license-status__label">Last check:</span>
			<span class="license-status__value">{{ formatDate(status.dateLastCheck) }}</span>
		</div>
		<div v-if="status.product" class="license-status__product-row">
			<span class="license-status__label">Product:</span>
			<span class="license-status__product" v-html="status.product" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { LicenseStatus } from '../../types/license'

const props = defineProps<{
	status: LicenseStatus | null
}>()

const statusClass = computed(() => {
	if (!props.status) return ''
	switch (props.status.statusKind) {
	case 'valid': return 'license-status__value--valid'
	case 'expired': return 'license-status__value--expired'
	case 'nolicense': return 'license-status__value--none'
	case 'userlimit': return 'license-status__value--warning'
	case 'check': return 'license-status__value--warning'
	default: return ''
	}
})

/**
 *
 * @param dateStr
 */
function formatDate(dateStr: string): string {
	try {
		return new Date(dateStr).toLocaleDateString('nl-NL')
	} catch {
		return dateStr
	}
}
</script>

<style scoped>
.license-status {
	margin-top: 12px;
	padding: 12px;
	background: var(--color-background-hover);
	border-radius: var(--border-radius);
}

.license-status__row {
	display: flex;
	gap: 8px;
	padding: 4px 0;
}

.license-status__label {
	font-weight: 500;
	min-width: 100px;
	color: var(--color-text-maxcontrast);
}

.license-status__value--valid {
	color: var(--color-success-text);
	font-weight: 600;
}

.license-status__value--expired,
.license-status__value--none {
	color: var(--color-error-text);
	font-weight: 600;
}

.license-status__value--warning {
	color: var(--color-warning);
	font-weight: 600;
}

.license-status__product-row {
	padding: 4px 0;
}

.license-status__product-row .license-status__label {
	display: block;
	margin-bottom: 4px;
}

.license-status__product :deep(table) {
	border-collapse: collapse;
}

.license-status__product :deep(td),
.license-status__product :deep(th) {
	padding: 4px 10px;
}
</style>
