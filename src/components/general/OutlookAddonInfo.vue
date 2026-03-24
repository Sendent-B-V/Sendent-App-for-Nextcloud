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
					<h3>{{ product.label }}</h3>
					<p>
						<strong>Latest version:</strong>
						{{ extractVersion(releases[product.slug].title) }}
					</p>
					<p v-if="releases[product.slug].date">
						<strong>Release date:</strong>
						{{ formatDate(releases[product.slug].date) }}
					</p>
					<div v-if="releases[product.slug].tags?.length" class="product-card__tags">
						<span v-for="tag in releases[product.slug].tags"
							:key="tag"
							class="product-card__tag">
							{{ tag }}
						</span>
					</div>
					<div class="product-card__actions">
						<button class="product-card__notes-toggle"
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
	{ slug: 'outlook-cross-platform', label: 'Sendent for Outlook (Cross-Platform)' },
	{ slug: 'ms-teams', label: 'Sendent for MS Teams' },
	{ slug: 'outlook-windows', label: 'Sendent for Outlook (Windows-Only)' },
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
	padding: 12px 0;
	color: var(--color-text-maxcontrast);
}

.product-releases__empty {
	color: var(--color-text-maxcontrast);
	padding: 12px 0;
}

.product-releases__grid {
	display: flex;
	flex-wrap: wrap;
	gap: 16px;
	margin-bottom: 24px;
}

.product-card {
	flex: 1;
	min-width: 280px;
	max-width: 400px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 16px;
}

.product-card h3 {
	font-size: 15px;
	font-weight: 600;
	margin-bottom: 8px;
}

.product-card p {
	margin: 4px 0;
	font-size: 14px;
}

.product-card__tags {
	display: flex;
	gap: 6px;
	margin-top: 6px;
	flex-wrap: wrap;
}

.product-card__tag {
	display: inline-block;
	padding: 2px 8px;
	font-size: 12px;
	border-radius: var(--border-radius-pill);
	background: var(--color-primary-element-light);
	color: var(--color-primary-element);
}

.product-card__actions {
	margin-top: 10px;
}

.product-card__notes-toggle {
	background: none;
	border: 1px solid var(--color-border-dark);
	border-radius: var(--border-radius);
	padding: 4px 12px;
	font-size: 13px;
	cursor: pointer;
	color: var(--color-primary-element);
}

.product-card__notes-toggle:hover {
	background: var(--color-background-hover);
}

.product-card__release-notes {
	margin-top: 12px;
	padding: 12px;
	background: var(--color-background-hover);
	border-radius: var(--border-radius);
	font-size: 14px;
	line-height: 1.6;
	overflow-x: auto;
}

.product-card__release-notes :deep(h2) {
	font-size: 14px;
	font-weight: 600;
	margin: 12px 0 6px;
}

.product-card__release-notes :deep(h2:first-child) {
	margin-top: 0;
}

.product-card__release-notes :deep(ul) {
	padding-left: 20px;
	margin: 6px 0;
}

.product-card__release-notes :deep(li) {
	margin: 4px 0;
}

.product-card__release-notes :deep(a) {
	color: var(--color-primary-element);
}
</style>