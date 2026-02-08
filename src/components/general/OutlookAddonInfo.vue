<template>
	<div class="addon-info">
		<h3>Outlook Add-in</h3>
		<div v-if="licenseStore.addinVersion" class="addon-info__details">
			<p><strong>Latest version:</strong> {{ licenseStore.addinVersion.Version }}</p>
			<p v-if="licenseStore.addinVersion.ReleaseDate">
				<strong>Release date:</strong> {{ formatDate(licenseStore.addinVersion.ReleaseDate) }}
			</p>
			<div class="addon-info__links">
				<a v-if="licenseStore.addinVersion.UrlBinary"
					:href="licenseStore.addinVersion.UrlBinary"
					class="button primary"
					target="_blank"
					rel="noopener">
					Download
				</a>
				<a v-if="licenseStore.addinVersion.UrlManual"
					:href="licenseStore.addinVersion.UrlManual"
					target="_blank"
					rel="noopener">
					Manual
				</a>
				<a v-if="licenseStore.addinVersion.UrlReleaseNotes"
					:href="licenseStore.addinVersion.UrlReleaseNotes"
					target="_blank"
					rel="noopener">
					Release Notes
				</a>
			</div>
		</div>
		<p v-else class="addon-info__empty">
			No add-in version information available.
		</p>
	</div>
</template>

<script setup lang="ts">
import { useLicenseStore } from '../../stores/license'

const licenseStore = useLicenseStore()

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
.addon-info {
	margin-bottom: 24px;
}

.addon-info h3 {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 12px;
}

.addon-info__links {
	display: flex;
	gap: 12px;
	margin-top: 8px;
}

.addon-info__empty {
	color: var(--color-text-maxcontrast);
}
</style>
