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
			{{ t('sendent', 'OFFLINE mode — release checks are disabled.') }}
		</div>
		<div v-else-if="loading" class="product-releases__loading">
			<span class="icon-loading" />
			{{ t('sendent', 'Loading release info …') }}
		</div>
		<div v-else class="product-releases__sections">
			<div v-if="fetchFailed" class="product-releases__error">
				{{ t('sendent', 'Release information could not be loaded. Please try again later.') }}
			</div>
			<section v-for="section in sections"
				:key="section.title"
				class="product-releases__section">
				<h3 class="product-releases__section-title">
					{{ section.title }}
				</h3>
				<div class="product-releases__grid">
					<div v-for="product in section.products"
						:key="product.slug"
						class="product-card"
						:class="'product-card--' + product.slug">
						<div class="product-card__content">
							<div class="product-card__header">
								<h4 class="product-card__name">
									{{ product.label }}
									<span v-if="product.isNew" class="product-card__label product-card__label--new">
										{{ t('sendent', 'New') }}
									</span>
									<span v-else-if="isRecentRelease(product.slug)" class="product-card__label product-card__label--update">
										{{ t('sendent', 'Update') }}
									</span>
								</h4>
								<p v-if="product.subtitle" class="product-card__subtitle" :title="product.subtitle">
									{{ product.subtitle }}
								</p>
								<div v-if="releases[product.slug]" class="product-card__badges">
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
								<p v-else class="product-card__unavailable">
									{{ t('sendent', 'Release information unavailable') }}
								</p>
							</div>

							<div v-if="releases[product.slug]?.date" class="product-card__info">
								<span class="product-card__info-label">{{ t('sendent', 'Released') }}</span>
								<span class="product-card__info-value"
									title="dd-MM-yyyy">{{ formatDate(releases[product.slug].date) }}</span>
							</div>

							<div class="product-card__footer">
								<button v-if="releases[product.slug]"
									class="button primary"
									@click="toggleNotes(product.slug)">
									{{ t('sendent', 'Release notes') }}
								</button>
								<a v-if="product.slug === 'outlook-windows' && releases[product.slug] && hasValidLicense"
									class="button primary product-card__download"
									:href="'https://download.sendent.com/addin/' + extractVersion(releases[product.slug].title) + '/Sendent_Outlook.zip'"
									target="_blank"
									rel="noopener noreferrer">
									{{ t('sendent', 'Download') }}
								</a>
								<a v-else-if="product.downloadUrl"
									class="button primary product-card__download"
									:href="product.downloadUrl"
									target="_blank"
									rel="noopener noreferrer">
									{{ t('sendent', 'Download') }}
								</a>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>

		<!-- Release notes modal -->
		<div v-if="activeNotesSlug"
			class="release-notes-modal__backdrop"
			@click.self="activeNotesSlug = ''">
			<div class="release-notes-modal">
				<div class="release-notes-modal__header">
					<h3>{{ activeProductLabel }} — {{ releases[activeNotesSlug]?.title }}</h3>
					<button class="release-notes-modal__close" @click="activeNotesSlug = ''">
						&#x2715;
					</button>
				</div>
				<!-- eslint-disable-next-line vue/no-v-html -- trusted first-party release-notes HTML from the Sendent backend API, not user input -->
				<div class="release-notes-modal__body"
					v-html="releases[activeNotesSlug]?.content" />
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { translate as t } from '@nextcloud/l10n'
import { ref, computed, watch } from 'vue'
import type { ReleaseEntry } from '../../types/releases'
import { fetchLatestReleases } from '../../services/releasesApi'
import { formatDate } from '../../utils/date-utils'
import { useLicenseStore } from '../../stores/license'

const mailProducts = [
	{ slug: 'outlook-windows', label: 'Outlook Classic (Windows)', subtitle: t('sendent', 'Only for Outlook Classic on Windows') },
	{ slug: 'outlook-cross-platform', label: 'Outlook (Cross-Platform)', subtitle: t('sendent', 'Windows, macOS and Outlook on the web') },
	{ slug: 'thunderbird', label: 'Thunderbird', subtitle: t('sendent', 'Windows, macOS and Linux'), downloadUrl: 'https://addons.thunderbird.net/en-US/thunderbird/addon/nextcloud-for-thunderbird/', isNew: true },
	{ slug: 'mac-mail', label: 'Mac Mail', subtitle: t('sendent', 'Apple Mail on macOS'), downloadUrl: 'https://apps.apple.com/app/nextcloud-for-mail/id6758224475', isNew: true },
]

const teamsProducts = [
	{ slug: 'ms-teams', label: 'Microsoft Teams', subtitle: t('sendent', 'Windows, macOS, Web, Android and iOS') },
]

const products = [...mailProducts, ...teamsProducts]

const licenseStore = useLicenseStore()
const releases = ref<Record<string, ReleaseEntry>>({})
const loading = ref(true)
const fetchFailed = ref(false)
const offlineMode = ref(false)
const hasValidLicense = computed(() => licenseStore.hasAnyValidLicense)
const activeNotesSlug = ref('')
const activeProductLabel = computed(() =>
	products.find(p => p.slug === activeNotesSlug.value)?.label ?? '',
)

// Every product always gets a card: one without release data (its changelog fetch failed)
// says so instead of disappearing, and keeps its public download link if it has one.
const sections = [
	{ title: t('sendent', 'Nextcloud for Email Suite'), products: mailProducts },
	{ title: t('sendent', 'Nextcloud for Microsoft Teams'), products: teamsProducts },
]

/**
 * Extracts a version number from a release title like "Release Notes v2.3.0"
 * @param title - The release title to extract the version from
 */
function extractVersion(title: string): string {
	const match = title.match(/v?(\d+\.\d+(?:\.\d+)?)/i)
	return match ? match[1] : title
}

const RECENT_RELEASE_DAYS = 14

/**
 * Whether the product's latest release is dated within the last RECENT_RELEASE_DAYS days.
 * @param slug - The product slug to check
 */
function isRecentRelease(slug: string): boolean {
	// Release dates look like "2026-10-22 00:00:00" and mean a calendar day, not a moment,
	// so compare whole days instead of parsing them as a (timezone-dependent) local time
	const match = releases.value[slug]?.date?.match(/^(\d{4})-(\d{2})-(\d{2})/)
	if (!match) return false
	const released = Date.UTC(Number(match[1]), Number(match[2]) - 1, Number(match[3]))
	const now = new Date()
	const today = Date.UTC(now.getFullYear(), now.getMonth(), now.getDate())
	const ageDays = (today - released) / (24 * 60 * 60 * 1000)
	// -1: viewers west of where the release was published can still be on the previous day
	return ageDays >= -1 && ageDays < RECENT_RELEASE_DAYS
}

/**
 * Opens the release-notes modal for the given product.
 * @param slug - The product slug whose release notes to show
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
		// The cards still render (download links work without release data); a notice explains the gaps
		fetchFailed.value = true
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

.product-releases__error {
	margin-bottom: 12px;
	padding: 8px 12px;
	font-size: 13px;
	border-radius: var(--border-radius-large);
	border-inline-start: 4px solid var(--color-warning);
	background: var(--color-background-hover);
	color: var(--color-main-text);
}

.product-releases__section {
	margin-bottom: 20px;
}

.product-releases__section-title {
	font-size: 13px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: var(--color-text-maxcontrast);
	margin: 0 0 8px 0;
}

.product-releases__grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
	gap: 12px;
}

.product-card {
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	display: flex;
	flex-direction: column;
	position: relative;
	transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.product-card::before {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	height: 3px;
	background: var(--color-primary-element);
	border-radius: var(--border-radius-large) var(--border-radius-large) 0 0;
}

.product-card--outlook-cross-platform::before,
.product-card--outlook-windows::before {
	background-color: #0078d4;
}

.product-card--thunderbird::before {
	background-color: #0a84ff;
}

.product-card--mac-mail::before {
	background-color: #34aadc;
}

.product-card--ms-teams::before {
	background-color: #6264a7;
}

.product-card:hover {
	border-color: var(--color-border-dark);
}

.product-card__content {
	padding: 12px 14px;
	display: flex;
	flex-direction: column;
	flex-grow: 1;
}

.product-card__header {
	margin-bottom: 8px;
}

.product-card__name {
	font-size: 14px;
	font-weight: 600;
	margin: 0 0 6px 0;
	color: var(--color-text-main);
}

.product-card__subtitle {
	margin: -4px 0 6px 0;
	font-size: 12px;
	line-height: 18px;
	color: var(--color-text-maxcontrast);
	/* Keep to one line so badges and dates line up across cards */
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.product-card__unavailable {
	margin: 0 0 10px 0;
	font-size: 12px;
	font-style: italic;
	color: var(--color-text-maxcontrast);
}

.product-card__label {
	display: inline-block;
	margin-inline-start: 6px;
	padding: 1px 7px;
	font-size: 10px;
	font-weight: 700;
	letter-spacing: 0.5px;
	text-transform: uppercase;
	vertical-align: middle;
	border-radius: var(--border-radius-pill);
}

.product-card__label--new {
	background-color: var(--color-success);
	color: var(--color-success-text, #fff);
}

.product-card__label--update {
	background-color: var(--color-info);
	color: var(--color-info-text, #fff);
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

.product-card__info {
	display: flex;
	align-items: baseline;
	gap: 6px;
	margin-bottom: 10px;
}

.product-card__info-label {
	font-size: 10px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: var(--color-text-maxcontrast);
	font-weight: 600;
}

.product-card__info-value {
	font-size: 12px;
	color: var(--color-text-main);
}

.product-card__footer {
	margin-top: auto;
	display: flex;
	gap: 8px;
}

.product-card__footer button {
	flex: 2;
	text-align: center;
}

.product-card__footer .product-card__download {
	flex: 1;
	text-decoration: none;
}

.product-card__footer button,
.product-card__footer .product-card__download {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	box-sizing: border-box;
	min-height: 32px;
	margin: 0;
	padding: 4px 12px;
	font-size: 13px;
	line-height: 1;
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

.release-notes-modal__body :deep(ul),
.release-notes-modal__body :deep(ol) {
	padding-left: 20px;
	margin: 8px 0;
}

.release-notes-modal__body :deep(ul) {
	list-style: disc;
}

.release-notes-modal__body :deep(ol) {
	list-style: decimal;
}

.release-notes-modal__body :deep(li) {
	margin: 6px 0;
	display: list-item;
}

.release-notes-modal__body :deep(a) {
	color: var(--color-primary-element);
}
</style>
