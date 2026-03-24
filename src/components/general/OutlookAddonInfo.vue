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
										class="product-card__tag">
										{{ tag }}
									</span>
								</div>
							</div>
						</div>
					</div>

					<div class="product-card__body">
						<div v-if="releases[product.slug].date" class="product-card__info">
							<span class="product-card__info-label">Released</span>
							<span class="product-card__info-value">{{ formatDate(releases[product.slug].date) }}</span>
						</div>
					</div>

					<div class="product-card__footer">
						<button class="button"
							:class="{ primary: !expandedNotes[product.slug] }"
							@click="toggleNotes(product.slug)">
							{{ expandedNotes[product.slug] ? 'Hide release notes' : 'View release notes' }}
						</button>
					</div>
				</div>

				<div v-if="expandedNotes[product.slug]"
					class="product-card__release-notes-wrapper">
					<div class="product-card__release-notes-content"
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
	background-color: var(--color-warning-light);
	color: var(--color-warning);
	border: 1px solid var(--color-warning);
	text-transform: uppercase;
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

.product-card__footer button {
	width: 100%;
}

.product-card__release-notes-wrapper {
	border-top: 1px solid var(--color-border-light);
	background: var(--color-background-hover);
	border-radius: 0 0 var(--border-radius-large) var(--border-radius-large);
}

.product-card__release-notes-content {
	padding: 20px 24px;
	font-size: 13px;
	line-height: 1.6;
	max-height: 300px;
	overflow-y: auto;
}

.product-card__release-notes-content :deep(h2) {
	font-size: 14px;
	font-weight: 700;
	margin: 16px 0 8px;
}

.product-card__release-notes-content :deep(h2:first-child) {
	margin-top: 0;
}

.product-card__release-notes-content :deep(ul) {
	padding-left: 20px;
	margin: 8px 0;
}

.product-card__release-notes-content :deep(li) {
	margin: 6px 0;
}

.product-card__release-notes-content :deep(a) {
	color: var(--color-primary-element);
}
</style>