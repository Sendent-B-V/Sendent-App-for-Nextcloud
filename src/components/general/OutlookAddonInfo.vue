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
	<div class="product-releases">
		<div v-if="offlineMode" class="product-releases__offline">
			OFFLINE mode — release checks are disabled.
		</div>
		<div v-else-if="loading" class="product-releases__loading">
			<span class="icon-loading" />
			Loading release info...
		</div>
		<div v-else-if="Object.keys(releases).length === 0" class="product-releases__empty">
			No release information available.
		</div>
		<div v-else class="product-releases__grid">
			<div v-for="product in products"
				:key="product.slug"
				class="product-card"
				:class="'product-card--' + product.slug">
				<div v-if="releases[product.slug]" class="product-card__content">
					<div class="product-card__header">
						<div class="product-card__title">
							<h3>{{ product.label }}</h3>
							<div class="product-card__badges">
								<div class="product-card__version-badge">
									v{{ extractVersion(releases[product.slug].title) }}
								</div>
								<div v-if="releases[product.slug].tags?.length" class="product-card__tags">
									<span v-for="tag in releases[product.slug].tags"
										:key="tag"
										class="product-card__tag"
										:class="{ 'product-card__tag--important': tag.toLowerCase() === 'important' }">
										{{ tag }}
									</span>
								</div>
							</div>
						</div>
					</div>

					<div class="product-card__body">
						<div v-if="releases[product.slug].date" class="product-card__info">
							<span class="product-card__info-label">Released</span>
							<span class="product-card__info-value"
								title="dd-MM-yyyy">{{ formatDate(releases[product.slug].date) }}</span>
						</div>
					</div>

					<div class="product-card__footer">
						<button class="button primary"
							@click="toggleNotes(product.slug)">
							View release notes
						</button>
						<a v-if="product.slug === 'outlook-windows' && hasValidLicense"
							class="button primary product-card__download"
							:href="'https://download.sendent.com/addin/' + extractVersion(releases[product.slug].title) + '/Sendent_Outlook.zip'"
							target="_blank"
							rel="noopener noreferrer">
							Download
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Release notes modal -->
		<div v-if="activeNotesSlug"
			class="release-notes-modal__backdrop"
			@click.self="activeNotesSlug = ''">
			<div class="release-notes-modal">
				<div class="release-notes-modal__header">
					<h3>{{ activeProductLabel }} — {{ releases[activeNotesSlug]?.title }}</h3>
					<button class="release-notes-modal__close" @click="activeNotesSlug = ''">&#x2715;</button>
				</div>
				<div class="release-notes-modal__body"
					v-html="releases[activeNotesSlug]?.content" />
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { ReleaseEntry } from '../../types/releases'
import { fetchLatestReleases } from '../../services/releasesApi'
import { formatDate } from '../../utils/date-utils'
import { useLicenseStore } from '../../stores/license'

const products = [
	{ slug: 'outlook-cross-platform', label: 'Outlook (Cross-Platform)' },
	{ slug: 'outlook-windows', label: 'Outlook (Windows)' },
	{ slug: 'ms-teams', label: 'MS Teams' },
]

const licenseStore = useLicenseStore()
const releases = ref<Record<string, ReleaseEntry>>({})
const loading = ref(true)
const offlineMode = ref(false)
const hasValidLicense = computed(() => licenseStore.hasAnyValidLicense)
const activeNotesSlug = ref('')
const activeProductLabel = computed(() =>
	products.find(p => p.slug === activeNotesSlug.value)?.label ?? '',
)

/**
 * Extracts a version number from a release title like "Release Notes v2.3.0"
 * @param title
 */
function extractVersion(title: string): string {
	const match = title.match(/v?(\d+\.\d+(?:\.\d+)?)/i)
	return match ? match[1] : title
}

/**
 * @param slug
 */
function toggleNotes(slug: string) {
	activeNotesSlug.value = slug
}

// Wait until the license store has finished loading before checking releases.
// This avoids a race where the component mounts before refreshStatus completes.
watch(() => licenseStore.loading, async (isLoading) => {
	if (isLoading) return
	if (licenseStore.email.includes('OFFLINE_')) {
		offlineMode.value = true
		loading.value = false
		return
	}
	try {
		releases.value = await fetchLatestReleases()
	} catch {
		// Silently fail — the empty state handles this
	} finally {
		loading.value = false
	}
}, { immediate: true })
</script>

<style scoped>
.product-releases__offline {
	color: var(--color-text-maxcontrast);
	padding: 32px;
	text-align: center;
	background: var(--color-background-hover);
	border-radius: var(--border-radius-large);
	border: 1px dashed var(--color-border);
	font-weight: 600;
}

.product-releases__loading {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 40px 0;
	color: var(--color-text-maxcontrast);
	justify-content: center;
}

.product-releases__empty {
	color: var(--color-text-maxcontrast);
	padding: 32px;
	text-align: center;
	background: var(--color-background-hover);
	border-radius: var(--border-radius-large);
	border: 1px dashed var(--color-border);
}

.product-releases__grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
	gap: 24px;
	margin-bottom: 32px;
}

.product-card {
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	display: flex;
	flex-direction: column;
	position: relative;
	transition: border-color 0.2s ease, box-shadow 0.2s ease;
	height: min-content;
}

.product-card::before {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	height: 4px;
	background: var(--color-primary-element);
	border-radius: var(--border-radius-large) var(--border-radius-large) 0 0;
}

.product-card--outlook-cross-platform::before,
.product-card--outlook-windows::before {
	background-color: #0078d4;
}

.product-card--ms-teams::before {
	background-color: #6264a7;
}

.product-card:hover {
	border-color: var(--color-border-dark);
}

.product-card__content {
	padding: 24px;
	display: flex;
	flex-direction: column;
	flex-grow: 1;
}

.product-card__header {
	margin-bottom: 20px;
}

.product-card__title h3 {
	font-size: 18px;
	font-weight: 600;
	margin: 0 0 10px 0;
	color: var(--color-text-main);
}

.product-card__badges {
	display: flex;
	gap: 8px;
	align-items: center;
	flex-wrap: wrap;
}

.product-card__version-badge {
	display: inline-flex;
	align-items: center;
	font-size: 12px;
	font-weight: 600;
	padding: 2px 10px;
	border-radius: var(--border-radius-pill);
	background: var(--color-primary-element-light);
	color: var(--color-primary-element);
}

.product-card__tags {
	display: flex;
	gap: 4px;
}

.product-card__tag {
	padding: 2px 8px;
	font-size: 11px;
	font-weight: 600;
	border-radius: var(--border-radius-pill);
	background-color: var(--color-primary-element-light);
	color: var(--color-primary-element);
	border: 1px solid var(--color-primary-element);
	text-transform: uppercase;
}

.product-card__tag--important {
	background-color: #fee;
	color: #c00;
	border-color: #c00;
}

.product-card__body {
	flex-grow: 1;
	margin-bottom: 24px;
}

.product-card__info {
	display: flex;
	flex-direction: column;
	gap: 2px;
}

.product-card__info-label {
	font-size: 11px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: var(--color-text-maxcontrast);
	font-weight: 600;
}

.product-card__info-value {
	font-size: 14px;
	color: var(--color-text-main);
}

.product-card__footer {
	margin-top: auto;
}

.product-card__footer {
	display: flex;
	gap: 8px;
}

.product-card__footer button {
	flex: 2;
	text-align: center;
}

.product-card__footer .product-card__download {
	flex: 1;
	text-align: center;
	text-decoration: none;
}

/* Release notes modal */
.release-notes-modal__backdrop {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.5);
	z-index: 9000;
	display: flex;
	align-items: center;
	justify-content: center;
}

.release-notes-modal {
	background: var(--color-main-background);
	border-radius: var(--border-radius-large);
	width: 640px;
	max-width: 90vw;
	max-height: 80vh;
	display: flex;
	flex-direction: column;
	box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
}

.release-notes-modal__header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 16px 24px;
	border-bottom: 1px solid var(--color-border);
}

.release-notes-modal__header h3 {
	margin: 0;
	font-size: 16px;
	font-weight: 600;
}

.release-notes-modal__close {
	background: none;
	border: none;
	font-size: 18px;
	cursor: pointer;
	color: var(--color-text-maxcontrast);
	padding: 4px 8px;
}

.release-notes-modal__close:hover {
	color: var(--color-text-main);
}

.release-notes-modal__body {
	padding: 20px 24px;
	font-size: 13px;
	line-height: 1.6;
	overflow-y: auto;
}

.release-notes-modal__body :deep(h2) {
	font-size: 14px;
	font-weight: 700;
	margin: 16px 0 8px;
}

.release-notes-modal__body :deep(h2:first-child) {
	margin-top: 0;
}

.release-notes-modal__body :deep(ul) {
	padding-left: 20px;
	margin: 8px 0;
	list-style: disc;
}

.release-notes-modal__body :deep(li) {
	margin: 6px 0;
	display: list-item;
}

.release-notes-modal__body :deep(a) {
	color: var(--color-primary-element);
}
</style>
