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
		<div v-if="loading" class="product-releases__loading">
			<span class="icon-loading" />
			Loading release info...
		</div>
		<div v-else-if="Object.keys(releases).length === 0" class="product-releases__empty">
			No release information available.
		</div>
		<div v-else class="product-releases__grid">
			<div v-for="product in products"
				:key="product.slug"
				class="product-card">
				<div v-if="releases[product.slug]" class="product-card__content">
					<div class="product-card__header">
						<div class="product-card__icon-wrapper" :class="'product-card__icon--' + product.slug">
							<span v-if="product.slug.includes('outlook')" class="icon-mail" />
							<span v-else-if="product.slug === 'ms-teams'" class="icon-contacts" />
							<span v-else class="icon-category-office" />
						</div>
						<div class="product-card__title">
							<h3>{{ product.label }}</h3>
							<div class="product-card__version-badge">
								{{ extractVersion(releases[product.slug].title) }}
							</div>
						</div>
					</div>

					<div class="product-card__body">
						<div v-if="releases[product.slug].date" class="product-card__info-row">
							<span class="product-card__info-label">Released:</span>
							<span class="product-card__info-value">{{ formatDate(releases[product.slug].date) }}</span>
						</div>

						<div v-if="releases[product.slug].tags?.length" class="product-card__tags">
							<span v-for="tag in releases[product.slug].tags"
								:key="tag"
								class="product-card__tag">
								{{ tag }}
							</span>
						</div>
					</div>

					<div class="product-card__actions">
						<button class="product-card__notes-toggle"
							:class="{ 'product-card__notes-toggle--expanded': expandedNotes[product.slug] }"
							@click="toggleNotes(product.slug)">
							{{ expandedNotes[product.slug] ? 'Hide release notes' : 'Show release notes' }}
						</button>
					</div>

					<div v-if="expandedNotes[product.slug]"
						class="product-card__release-notes"
						v-html="releases[product.slug].content" />
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import type { ReleaseEntry } from '../../types/releases'
import { fetchLatestReleases } from '../../services/releasesApi'
import { formatDate } from '../../utils/date-utils'

const products = [
	{ slug: 'outlook-cross-platform', label: 'Outlook (Cross-Platform)' },
	{ slug: 'ms-teams', label: 'MS Teams' },
	{ slug: 'outlook-windows', label: 'Outlook (Windows)' },
]

const releases = ref<Record<string, ReleaseEntry>>({})
const loading = ref(true)
const expandedNotes = reactive<Record<string, boolean>>({})

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
	expandedNotes[slug] = !expandedNotes[slug]
}

onMounted(async () => {
	try {
		releases.value = await fetchLatestReleases()
	} catch {
		// Silently fail — the empty state handles this
	} finally {
		loading.value = false
	}
})
</script>

<style scoped>
.product-releases__loading {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 24px 0;
	color: var(--color-text-maxcontrast);
	justify-content: center;
}

.product-releases__empty {
	color: var(--color-text-maxcontrast);
	padding: 24px;
	text-align: center;
	background: var(--color-background-hover);
	border-radius: var(--border-radius-large);
}

.product-releases__grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 20px;
	margin-bottom: 32px;
}

.product-card {
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 20px;
	display: flex;
	flex-direction: column;
	transition: border-color 0.2s ease;
}

.product-card:hover {
	border-color: var(--color-border-dark);
}

.product-card__header {
	display: flex;
	align-items: flex-start;
	gap: 16px;
	margin-bottom: 16px;
}

.product-card__icon-wrapper {
	width: 44px;
	height: 44px;
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
	font-size: 24px;
	background-color: var(--color-background-hover);
}

.product-card__icon--outlook-cross-platform,
.product-card__icon--outlook-windows {
	color: #0078d4;
	background-color: rgba(0, 120, 212, 0.1);
}

.product-card__icon--ms-teams {
	color: #6264a7;
	background-color: rgba(98, 100, 167, 0.1);
}

.product-card__title {
	flex: 1;
}

.product-card h3 {
	font-size: 16px;
	font-weight: 600;
	margin: 0 0 4px 0;
	line-height: 1.2;
}

.product-card__version-badge {
	display: inline-block;
	font-size: 12px;
	font-weight: 700;
	padding: 1px 8px;
	border-radius: var(--border-radius-pill);
	background: var(--color-primary-element-light);
	color: var(--color-primary-element);
}

.product-card__body {
	flex: 1;
}

.product-card__info-row {
	display: flex;
	justify-content: space-between;
	font-size: 13px;
	margin-bottom: 8px;
}

.product-card__info-label {
	color: var(--color-text-maxcontrast);
}

.product-card__info-value {
	font-weight: 500;
}

.product-card__tags {
	display: flex;
	gap: 4px;
	margin-top: 12px;
	flex-wrap: wrap;
}

.product-card__tag {
	display: inline-block;
	padding: 2px 8px;
	font-size: 11px;
	border-radius: var(--border-radius-pill);
	background: var(--color-background-hover);
	color: var(--color-text-maxcontrast);
	border: 1px solid var(--color-border);
}

.product-card__actions {
	margin-top: 20px;
	padding-top: 16px;
	border-top: 1px solid var(--color-border-light);
}

.product-card__notes-toggle {
	width: 100%;
	background: transparent;
	border: 1px solid var(--color-primary-element);
	border-radius: var(--border-radius);
	padding: 6px 16px;
	font-size: 13px;
	font-weight: 500;
	cursor: pointer;
	color: var(--color-primary-element);
	transition: all 0.2s ease;
}

.product-card__notes-toggle:hover {
	background: var(--color-primary-element);
	color: var(--color-main-background);
}

.product-card__notes-toggle--expanded {
	background: var(--color-background-hover);
	border-color: var(--color-border);
	color: var(--color-text-main);
}

.product-card__release-notes {
	margin-top: 16px;
	padding: 16px;
	background: var(--color-background-hover);
	border-radius: var(--border-radius-large);
	font-size: 13px;
	line-height: 1.6;
	overflow-x: auto;
	border: 1px solid var(--color-border-light);
}

.product-card__release-notes :deep(h2) {
	font-size: 14px;
	font-weight: 700;
	margin: 16px 0 8px;
	color: var(--color-text-main);
}

.product-card__release-notes :deep(h2:first-child) {
	margin-top: 0;
}

.product-card__release-notes :deep(ul) {
	padding-left: 18px;
	margin: 8px 0;
}

.product-card__release-notes :deep(li) {
	margin: 6px 0;
}

.product-card__release-notes :deep(a) {
	color: var(--color-primary-element);
	text-decoration: underline;
}

.product-card__release-notes :deep(code) {
	background: var(--color-background-dark);
	padding: 2px 4px;
	border-radius: 3px;
	font-family: monospace;
}
</style>